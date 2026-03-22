<?php

namespace App\Http\Controllers\SupportTeam;

use App\Helpers\Qs;
use App\Helpers\Mk;
use App\Http\Requests\Mark\MarkSelector;
use App\Models\Setting;
use App\Repositories\ExamRepo;
use App\Repositories\MarkRepo;
use App\Repositories\MyClassRepo;
use App\Http\Controllers\Controller;
use App\Repositories\StudentRepo;
use App\Models\Grade;
use App\Models\MarkConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use App\Models\MyClass;

class MarkController extends Controller
{
    protected $my_class, $exam, $student, $year, $user, $mark;

    public function __construct(MyClassRepo $my_class, ExamRepo $exam, StudentRepo $student, MarkRepo $mark)
    {
        $this->exam =  $exam;
        $this->mark =  $mark;
        $this->student =  $student;
        $this->my_class =  $my_class;
        // DO NOT resolve current_session here: controller may be constructed before tenancy initializes.
        // Always resolve session inside the request handlers.
        $this->year = null;

       // $this->middleware('teamSAT', ['except' => ['show', 'year_selected', 'year_selector', 'print_view'] ]);
    }

    public function index()
    {
        $d['terms'] = [1 => __('Term 1'), 2 => __('Term 2')];
        $d['my_classes'] = $this->my_class->all();
        $d['sections'] = $this->my_class->getAllSections();
        $d['subjects'] = $this->my_class->getAllSubjects();
        $d['selected'] = false;

        return view('pages.support_team.marks.index', $d);
    }

    public function year_selector($student_id)
    {
        $student_id = Qs::decodeHash($student_id);
        if ($student_id === null) {
            abort(404);
        }
        return $this->verifyStudentExamYear($student_id);
    }

    public function year_selected(Request $req, $student_id)
    {
        $student_id = Qs::decodeHash($student_id);
        if ($student_id === null) {
            abort(404);
        }
        if(!$this->verifyStudentExamYear($student_id, $req->year)){
            return $this->noStudentRecord();
        }

        $student_id = Qs::hash($student_id);
        return redirect()->route('marks.show', [$student_id, $req->year]);
    }

    public function show($student_id, $year)
    {
        $student_id = Qs::decodeHash($student_id);
        if ($student_id === null) {
            abort(404);
        }
        /* Prevent Other Students/Parents from viewing Result of others */
        if(Auth::user()->id != $student_id && !Qs::userIsTeamSAT() && !Qs::userIsMyChild($student_id, Auth::user()->id)){
            return redirect(route('dashboard'))->with('pop_error', __('msg.denied'));
        }

        if(Mk::examIsLocked() && !Qs::userIsTeamSA()){
            Session::put('marks_url', route('marks.show', [Qs::hash($student_id), $year]));

            if(!$this->checkPinVerified($student_id)){
                return redirect()->route('pins.enter', Qs::hash($student_id));
            }
        }

        if(!$this->verifyStudentExamYear($student_id, $year)){
            return $this->noStudentRecord();
        }

        $wh = ['student_id' => $student_id, 'year' => $year];
        $d['marks'] = $this->exam->getMark($wh);
        $d['exam_records'] = $exr = $this->exam->getRecord($wh);
        $d['terms'] = [1 => __('Term 1'), 2 => __('Term 2')];
        $d['sr'] = $this->student->getRecord(['user_id' => $student_id])->first();
        $d['my_class'] = $mc = $this->my_class->getMC(['id' => $exr->first()->my_class_id])->first();
        $d['class_type'] = $this->my_class->findTypeByClass($mc->id);
        $d['subjects'] = $this->my_class->findSubjectByClass($mc->id);
        $d['year'] = $year;
        $d['student_id'] = $student_id;
        //$d['ct'] = $d['class_type']->code;
        //$d['mark_type'] = Qs::getMarkType($d['ct']);

        return view('pages.support_team.marks.show.index', $d);
    }

    public function print_view($student_id, $term, $year)
    {
        $student_id = Qs::decodeHash($student_id);
        if ($student_id === null) {
            abort(404);
        }
        /* Prevent Other Students/Parents from viewing Result of others */
        if(Auth::user()->id != $student_id && !Qs::userIsTeamSA() && !Qs::userIsMyChild($student_id, Auth::user()->id)){
            return redirect(route('dashboard'))->with('pop_error', __('msg.denied'));
        }

        if(Mk::examIsLocked() && !Qs::userIsTeamSA()){
            Session::put('marks_url', route('marks.show', [Qs::hash($student_id), $year]));

            if(!$this->checkPinVerified($student_id)){
                return redirect()->route('pins.enter', Qs::hash($student_id));
            }
        }

        if(!$this->verifyStudentExamYear($student_id, $year)){
            return $this->noStudentRecord();
        }

        $wh = ['student_id' => $student_id, 'term' => $term, 'year' => $year];
        $d['marks'] = $mks = $this->exam->getMark($wh);
        $d['exr'] = $exr = $this->exam->getRecord($wh)->first();
        $d['my_class'] = $mc = $this->my_class->find($exr->my_class_id);
        $d['section_id'] = $exr->section_id;
        $d['term'] = (int) $term;
        $d['tex'] = 'tex' . $term;
        $d['sr'] = $sr = $this->student->getRecord(['user_id' => $student_id])->first();
        if ($sr) {
            $sr->loadMissing('section');
        }
        $d['class_type'] = $this->my_class->findTypeByClass($mc->id);
        $d['subjects'] = $this->my_class->findSubjectByClass($mc->id);

        $d['ct'] = $ct = $d['class_type']->code;
        $d['year'] = $year;
        $d['student_id'] = $student_id;

        $d['s'] = Setting::all()->flatMap(function($s){
            return [$s->type => $s->description];
        });

        $class_type_id = (int) ($d['class_type']->id ?? 0);
        $mark_config = $class_type_id > 0
            ? MarkConfig::with('template')
                ->where('class_type_id', $class_type_id)
                ->where('term_id', (int) $term)
                ->where('school_year', $year)
                ->first()
            : null;
        $d['mark_slots'] = $mark_config
            ? $mark_config->slotsForDisplay()
            : (new \App\Models\MarkTemplate())->slotsForDisplay();

        $d['term_grade'] = $class_type_id > 0
            ? $this->resolveGradeForSubjectAverage(
                isset($exr->ave) && is_numeric($exr->ave) ? (float) $exr->ave : null,
                $class_type_id
            )
            : null;

        return view('pages.support_team.marks.print.marksheet', $d);
    }

    /**
     * Full-year combined result card for one student (Term 1 + Term 2 + annual summary / ranks).
     */
    public function print_annual_student($student_id, $year)
    {
        $student_id = Qs::decodeHash($student_id);
        if ($student_id === null) {
            abort(404);
        }

        if (Auth::user()->id != $student_id && ! Qs::userIsTeamSA() && ! Qs::userIsMyChild($student_id, Auth::user()->id)) {
            return redirect(route('dashboard'))->with('pop_error', __('msg.denied'));
        }

        if (Mk::examIsLocked() && ! Qs::userIsTeamSA()) {
            Session::put('marks_url', route('marks.show', [Qs::hash($student_id), $year]));

            if (! $this->checkPinVerified($student_id)) {
                return redirect()->route('pins.enter', Qs::hash($student_id));
            }
        }

        if (! $this->verifyStudentExamYear($student_id, $year)) {
            return $this->noStudentRecord();
        }

        $sr = $this->student->getRecord(['user_id' => $student_id])->first();
        if (! $sr) {
            abort(404);
        }
        $sr->loadMissing('section');

        $class_id = (int) $sr->my_class_id;
        $section_id = (int) $sr->section_id;

        $built = $this->buildAnnualSectionData($class_id, $section_id, $year);
        $my = [
            'term1_total' => null,
            'term2_total' => null,
            'term1_avg' => null,
            'term2_avg' => null,
            'annual_avg' => null,
            'rank_term1' => null,
            'rank_term2' => null,
            'rank' => null,
        ];
        $marks_term1 = [];
        $marks_term2 = [];
        if ($built !== null) {
            $marks_term1 = $built['marks_term1'];
            $marks_term2 = $built['marks_term2'];
            if (isset($built['annual_stats'][$student_id])) {
                $my = array_merge($my, $built['annual_stats'][$student_id]);
            }
        }

        $subjects = $this->my_class->findSubjectByClass($class_id);
        if (! $subjects || $subjects->count() < 1) {
            $sub_ids = $built['sub_ids'] ?? collect();
            $subjects = $sub_ids->count() > 0
                ? $this->my_class->getSubjectsByIDs($sub_ids)
                : collect();
        }

        $subjectRankBySubId = [];
        if ($built !== null) {
            $stIds = $built['st_ids'];
            foreach ($subjects as $sub) {
                $sid = $sub->id;
                $allRanks = $this->denseRanksDescending($stIds, function ($stId) use ($sid, $marks_term1, $marks_term2) {
                    $a = $marks_term1[$stId][$sid] ?? null;
                    $b = $marks_term2[$stId][$sid] ?? null;
                    if ($a !== null && $b !== null) {
                        return (float) $a + (float) $b;
                    }
                    if ($a !== null) {
                        return (float) $a;
                    }
                    if ($b !== null) {
                        return (float) $b;
                    }

                    return null;
                });
                $subjectRankBySubId[$sid] = $allRanks[$student_id] ?? null;
            }
        }

        $subject_rows = [];
        foreach ($subjects as $sub) {
            $sid = $sub->id;
            $t1 = $marks_term1[$student_id][$sid] ?? null;
            $t2 = $marks_term2[$student_id][$sid] ?? null;
            $avg = ($t1 !== null && $t2 !== null) ? round(((float) $t1 + (float) $t2) / 2, 1) : null;
            $total = null;
            if ($t1 !== null && $t2 !== null) {
                $total = round((float) $t1 + (float) $t2, 1);
            } elseif ($t1 !== null) {
                $total = round((float) $t1, 1);
            } elseif ($t2 !== null) {
                $total = round((float) $t2, 1);
            }
            $subject_rows[] = [
                'subject' => $sub,
                't1' => $t1,
                't2' => $t2,
                'total' => $total,
                'avg' => $avg,
                'sub_rank' => $subjectRankBySubId[$sid] ?? null,
            ];
        }

        $class_type = $this->my_class->findTypeByClass($class_id);
        $ctId = (int) ($class_type->id ?? 0);

        $d['sr'] = $sr;
        $d['my_class'] = $this->my_class->find($class_id);
        $d['class_type'] = $class_type;
        $d['year'] = $year;
        $d['student_id'] = $student_id;
        $d['subject_rows'] = $subject_rows;
        $d['summary'] = $my;
        $d['cum_total'] = ($my['term1_total'] !== null || $my['term2_total'] !== null)
            ? ((int) ($my['term1_total'] ?? 0) + (int) ($my['term2_total'] ?? 0))
            : null;

        $d['annual_grade'] = $ctId > 0
            ? $this->resolveGradeForSubjectAverage(
                isset($my['annual_avg']) && is_numeric($my['annual_avg']) ? (float) $my['annual_avg'] : null,
                $ctId
            )
            : null;

        $d['s'] = Setting::all()->flatMap(function ($s) {
            return [$s->type => $s->description];
        });

        return view('pages.support_team.marks.print.annual_student', $d);
    }

    public function selector(MarkSelector $req)
    {
        // Resolve the active academic session/year once per request.
        $year = $this->resolveActiveYear();

        $data = $req->only(['term', 'my_class_id', 'section_id', 'subject_id']);
        $d2 = $req->only(['term', 'my_class_id', 'section_id']);
        $d = $req->only(['my_class_id', 'section_id']);
        $data['year'] = $d2['year'] = $year;
        $d['session'] = $year;

        $students = $this->student->getRecord($d)->get();
        if ($students->count() < 1) {
            $students = $this->student->getRecord(['my_class_id' => $d['my_class_id'], 'section_id' => $d['section_id']])->get();
        }
        if ($students->count() < 1) {
            return back()->with('pop_error', __('msg.rnf'));
        }

        foreach ($students as $s) {
            $data['student_id'] = $d2['student_id'] = $s->user_id;
            $this->exam->createMark($data);
            $this->exam->createRecord($d2);
        }

        return redirect()->route('marks.manage', [$req->term, $req->my_class_id, $req->section_id, $req->subject_id]);
    }

    public function manage($term, $class_id, $section_id, $subject_id)
    {
        // Resolve and strictly enforce a non-null academic session/year.
        $year = $this->resolveActiveYear();
        $d = ['term' => $term, 'my_class_id' => $class_id, 'section_id' => $section_id, 'subject_id' => $subject_id, 'year' => $year];

        $d['marks'] = $this->exam->getMark($d);

        if ($d['marks']->count() < 1) {
            $students = $this->student->getRecord([
                'my_class_id' => $class_id,
                'section_id'  => $section_id,
                'session'     => $year,
            ])->get();
            if ($students->count() < 1) {
                $students = $this->student->getRecord([
                    'my_class_id' => $class_id,
                    'section_id'  => $section_id,
                ])->get();
            }
            if ($students->count() < 1) {
                return $this->noStudentRecord();
            }
            $data = $d;
            $d2 = ['term' => $term, 'my_class_id' => $class_id, 'section_id' => $section_id, 'year' => $year];
            foreach ($students as $s) {
                $data['student_id'] = $d2['student_id'] = $s->user_id;
                $this->exam->createMark($data);
                $this->exam->createRecord($d2);
            }
            $d['marks'] = $this->exam->getMark($d);
        }

        $d['m'] = $d['marks']->first();
        $d['terms'] = [1 => __('Term 1'), 2 => __('Term 2')];
        $d['my_classes'] = $this->my_class->all();
        $d['sections'] = $this->my_class->getAllSections();
        $d['subjects'] = $this->my_class->getAllSubjects();
        if(Qs::userIsTeacher()){
            $d['subjects'] = $this->my_class->findSubjectByTeacher(Auth::user()->id)->where('my_class_id', $class_id);
        }
        $d['selected'] = true;
        $mc = MyClass::with('class_type')->find($class_id);
        $class_type_id = $mc ? $mc->class_type_id : null;
        $d['class_type'] = $mc ? $mc->class_type : null;
        $levelName = $d['class_type'] ? $d['class_type']->name : ('Class ID ' . $class_id);

        $termId = (int) $term;
        $school_year = trim((string) $year);

        $d['mark_config'] = null;
        if ($class_type_id !== null) {
            $d['mark_config'] = MarkConfig::with('template')
                ->where('class_type_id', (int) $class_type_id)
                ->where('term_id', $termId)
                ->where('school_year', $school_year)
                ->first();
        }

        if (!$d['mark_config'] || !$d['mark_config']->mark_template_id || !$d['mark_config']->template) {
            $anyYear = null;
            if ($class_type_id !== null) {
                $anyYear = MarkConfig::where('class_type_id', (int) $class_type_id)
                    ->where('term_id', $termId)
                    ->first();
            }

            Log::warning('Marks scheme mapping not found', [
                'my_class_id' => (int) $class_id,
                'class_type_id' => $class_type_id,
                'term_id' => $termId,
                'school_year' => $school_year,
                'found_any_year' => (bool) $anyYear,
                'any_year_value' => $anyYear ? $anyYear->school_year : null,
                'any_template_id' => $anyYear ? $anyYear->mark_template_id : null,
            ]);

            $d['no_blueprint'] = true;
            $d['no_blueprint_msg'] = 'Grading scheme not set for Level: ' . $levelName . ', Term: ' . $termId . ', Year: ' . $school_year . '.';
            if ($anyYear && $anyYear->school_year && $anyYear->school_year !== $school_year) {
                $d['no_blueprint_msg'] .= ' Mapping exists for Year: ' . $anyYear->school_year . '.';
            }
            return view('pages.support_team.marks.manage', $d);
        }

        return view('pages.support_team.marks.manage', $d);
    }

    public function update(Request $req, $term, $class_id, $section_id, $subject_id)
    {
        // Always use the same enforced session/year as manage()/selector().
        $year = $this->resolveActiveYear();
        $p = ['term' => $term, 'my_class_id' => $class_id, 'section_id' => $section_id, 'subject_id' => $subject_id, 'year' => $year];

        $class_type = $this->my_class->findTypeByClass($class_id);
        $class_type_id = $class_type ? $class_type->id : null;
        if ($class_type_id === null) {
            $firstType = \App\Models\ClassType::orderBy('id')->first();
            $class_type_id = $firstType ? $firstType->id : null;
        }
        $config = MarkConfig::with('template')
            ->where('class_type_id', $class_type_id)
            ->where('term_id', (int) $term)
            ->where('school_year', $year)
            ->first();
        if (!$config || !$config->template) {
            $mc = MyClass::with('class_type')->find($class_id);
            $levelName = $mc && $mc->class_type ? $mc->class_type->name : ('Class ID ' . $class_id);
            return response()->json(['msg' => 'Grading scheme not set for Level: ' . $levelName . ', Term: ' . (int) $term . ', Year: ' . $year . '.'], 422);
        }
        $displaySlots = $config->slotsForDisplay();
        $activeSlotIndex = (int) ($config->active_slot ?? 0);
        $activeKey = null;
        $activeMax = 0;
        foreach ($displaySlots as $s) {
            if (($s['slot_index'] ?? 0) === $activeSlotIndex) {
                $activeKey = $s['key'];
                $activeMax = (int) ($s['max'] ?? 0);
                break;
            }
        }
        $slotMax = [];
        foreach ($displaySlots as $s) {
            $slotMax[$s['key']] = (int) ($s['max'] ?? 0);
        }

        $d = $d3 = $all_st_ids = [];
        $marks = $this->exam->getMark($p);
        $class_type = $this->my_class->findTypeByClass($class_id);
        $mks = $req->all();
        $term = (int) $term;

        foreach ($marks->sortBy('user.name') as $mk) {
            $all_st_ids[] = $mk->student_id;
            // Build payload dynamically from the active template slot keys.
            // This keeps marks entry compatible with templates that have >5 components (t5..t10).
            $d = [];
            foreach ($slotMax as $key => $_max) {
                $d[$key] = $mk->{$key} ?? null;
            }
            $d['tca'] = 0;

            // Unlock: allow saving scores for ALL assessment inputs (t1..tN + exm).
            // Inputs are named like: {slot_key}_{mark_id}.
            foreach ($slotMax as $key => $maxAllowed) {
                $inputKey = $key . '_' . $mk->id;
                if (!array_key_exists($inputKey, $mks)) {
                    continue;
                }

                $raw = $mks[$inputKey];
                $val = ($raw === '' || $raw === null) ? null : (int) $raw;

                if ($val !== null && ($val < 0 || $val > (int) $maxAllowed)) {
                    if ($key === 'exm') {
                        return response()->json(['msg' => __('Exam score must be between 0 and :max', ['max' => (int) $maxAllowed])], 422);
                    }
                    return response()->json(['msg' => __('Score must be between 0 and :max', ['max' => (int) $maxAllowed])], 422);
                }

                $d[$key] = $val;
            }

            $tca = 0;
            foreach ($slotMax as $key => $_max) {
                if ($key === 'exm') {
                    continue;
                }
                $v = $d[$key] ?? null;
                if ($v !== null) {
                    $tca += (int) $v;
                }
            }
            $exm = $d['exm'] ?? null;
            $examMax = (int) ($slotMax['exm'] ?? 0);
            if ($exm !== null && ($exm < 0 || $exm > $examMax)) {
                return response()->json(['msg' => __('Exam score must be between 0 and :max', ['max' => $examMax])], 422);
            }
            $d['tca'] = $tca;
            $total = $tca + (int) $exm;
            $d['tex' . $term] = $total;

            if ($total > $config->totalMax()) {
                $d['tex' . $term] = null;
                foreach ($slotMax as $key => $_max) {
                    if ($key === 'exm') {
                        continue;
                    }
                    $d[$key] = null;
                }
                $d['tca'] = null;
                $d['exm'] = null;
            }

            $d['grade_id'] = null;
            if ($d['tex' . $term] !== null) {
                $grade = $this->mark->getGrade($total, $class_type->id);
                $d['grade_id'] = $grade ? $grade->id : null;
            }

            $this->exam->updateMark($mk->id, $d);
        }

        /** Sub Position Begin  **/
        foreach ($marks->sortBy('user.name') as $mk) {
            $d2['sub_pos'] = $this->mark->getSubPos($mk->student_id, $term, $class_id, $subject_id, $year);
            $this->exam->updateMark($mk->id, $d2);
        }

        /* Exam Record Update */
        unset($p['subject_id']);
        foreach ($all_st_ids as $st_id) {
            $p['student_id'] = $st_id;
            $d3['total'] = $this->mark->getExamTotalTerm($term, $st_id, $class_id, $year);
            $d3['ave'] = $this->mark->getExamAvgTerm($term, $st_id, $class_id, $section_id, $year);
            $d3['class_ave'] = $this->mark->getClassAvg($term, $class_id, $year);
            $d3['pos'] = $this->mark->getPos($st_id, $term, $class_id, $section_id, $year);
            $this->exam->updateRecord($p, $d3);
        }

        return Qs::jsonUpdateOk();
    }

    public function batch_fix()
    {
        $d['terms'] = [1 => __('Term 1'), 2 => __('Term 2')];
        $d['my_classes'] = $this->my_class->all();
        $d['sections'] = $this->my_class->getAllSections();
        $d['selected'] = false;

        return view('pages.support_team.marks.batch_fix', $d);
    }

    public function batch_update(Request $req): \Illuminate\Http\JsonResponse
    {
        $term = (int) $req->term;
        $class_id = $req->my_class_id;
        $section_id = $req->section_id;

        $year = $this->resolveActiveYear();

        $w = ['term' => $term, 'my_class_id' => $class_id, 'section_id' => $section_id, 'year' => $year];

        $exrs = $this->exam->getRecord($w);
        $marks = $this->exam->getMark($w);

        $class_type = $this->my_class->findTypeByClass($class_id);
        $tex = 'tex' . $term;

        foreach ($marks as $mk) {
            $total = $mk->$tex;
            $d['grade_id'] = $this->mark->getGrade($total, $class_type->id);
            $this->exam->updateMark($mk->id, $d);
        }

        foreach ($exrs as $exr) {
            $st_id = $exr->student_id;
            $d3['total'] = $this->mark->getExamTotalTerm($term, $st_id, $class_id, $year);
            $d3['ave'] = $this->mark->getExamAvgTerm($term, $st_id, $class_id, $section_id, $year);
            $d3['class_ave'] = $this->mark->getClassAvg($term, $class_id, $year);
            $d3['pos'] = $this->mark->getPos($st_id, $term, $class_id, $section_id, $year);
            $this->exam->updateRecord(['id' => $exr->id], $d3);
        }

        return Qs::jsonUpdateOk();
    }

    public function tabulation($term = null, $class_id = null, $section_id = null)
    {
        $year = $this->resolveActiveYear();

        $d['my_classes'] = $this->my_class->all();
        $d['terms'] = [
            1 => __('Term 1'),
            2 => __('Term 2'),
            'annual' => __('Annual Report (Full Year Summary)'),
        ];
        $d['selected'] = false;

        if ($class_id && $term && $section_id) {
            $isAnnual = is_string($term) && strtolower($term) === 'annual';

            if ($isAnnual) {
                $built = $this->buildAnnualSectionData((int) $class_id, (int) $section_id, $year);
                if ($built === null) {
                    return Qs::goWithDanger('marks.tabulation', __('msg.srnf'));
                }

                $sub_ids = $built['sub_ids'];
                $st_ids = $built['st_ids'];
                $marksTerm1 = $built['marks_term1'];
                $marksTerm2 = $built['marks_term2'];
                $annualStats = $built['annual_stats'];

                $d['my_class_id'] = $class_id;
                $d['section_id'] = $section_id;
                $d['my_class'] = $mc = $this->my_class->find($class_id);
                $d['section'] = $this->my_class->findSection($section_id);
                $d['term'] = 'annual';
                $d['year'] = $year;

                // Required by annual roster matrix/header rendering.
                // Primary source: all subjects attached to the selected class.
                $d['subjects'] = $this->my_class->findSubjectByClass($class_id);
                // Safety fallback: if class-subject mapping is empty, use subjects found in marks.
                if (!$d['subjects'] || $d['subjects']->count() < 1) {
                    $d['subjects'] = $this->my_class->getSubjectsByIDs($sub_ids);
                }

                // Needed by the Blade selection form (section dropdown uses $sections->where(...))
                $d['sections'] = $this->my_class->getAllSections();
                $d['students'] = $this->student->getRecordByUserIDs($st_ids)->get()->sortBy('user.name');

                $d['annual_stats'] = $annualStats;
                $d['rank_term1'] = $built['rank_term1'];
                $d['rank_term2'] = $built['rank_term2'];
                $d['rank_annual'] = $built['rank_annual'];
                // Build roster matrix payload for 3-row-per-student UI.
                $rosterRows = [];
                foreach ($d['students'] as $st) {
                    $stId = $st->user_id;
                    $row = [
                        'student_id' => $stId,
                        'name' => $st->user->name,
                        'sex' => $this->rosterSexShort($st->user),
                        'adm_no' => ($st->adm_no !== null && $st->adm_no !== '') ? (string) $st->adm_no : '-',
                        'sem1' => [],
                        'sem2' => [],
                        'avg' => [],
                        'term1_total' => $annualStats[$stId]['term1_total'] ?? null,
                        'term2_total' => $annualStats[$stId]['term2_total'] ?? null,
                        'term1_avg' => $annualStats[$stId]['term1_avg'] ?? null,
                        'term2_avg' => $annualStats[$stId]['term2_avg'] ?? null,
                        'annual_avg' => $annualStats[$stId]['annual_avg'] ?? null,
                        'rank_term1' => $annualStats[$stId]['rank_term1'] ?? null,
                        'rank_term2' => $annualStats[$stId]['rank_term2'] ?? null,
                        'rank' => $annualStats[$stId]['rank'] ?? null,
                    ];
                    foreach ($d['subjects'] as $sub) {
                        $sid = $sub->id;
                        $t1 = $marksTerm1[$stId][$sid] ?? null;
                        $t2 = $marksTerm2[$stId][$sid] ?? null;
                        $a = ($t1 !== null && $t2 !== null) ? round(((float) $t1 + (float) $t2) / 2, 1) : null;
                        $row['sem1'][$sid] = $t1;
                        $row['sem2'][$sid] = $t2;
                        $row['avg'][$sid] = $a;
                    }
                    $rosterRows[] = $row;
                }
                $d['roster_rows'] = $rosterRows;
                $d['selected'] = true;

                return view('pages.support_team.marks.tabulation.index', $d);
            }

            $termId = (int) $term;
            $wh = ['my_class_id' => $class_id, 'section_id' => $section_id, 'term' => $term, 'year' => $year];

            $sub_ids = $this->mark->getSubjectIDs($wh);
            $st_ids = $this->mark->getStudentIDs($wh);

            if (count($sub_ids) < 1 || count($st_ids) < 1) {
                return Qs::goWithDanger('marks.tabulation', __('msg.srnf'));
            }

            $d['subjects'] = $this->my_class->getSubjectsByIDs($sub_ids);
            $d['students'] = $this->student->getRecordByUserIDs($st_ids)->get()->sortBy('user.name');
            $d['sections'] = $this->my_class->getAllSections();
            $d['selected'] = true;
            $d['my_class_id'] = $class_id;
            $d['section_id'] = $section_id;
            $d['term'] = $termId;
            $d['year'] = $year;
            $d['my_class'] = $mc = $this->my_class->find($class_id);
            $d['section'] = $this->my_class->findSection($section_id);

            // Blueprint slots (grading scheme components) for this level + term.
            $classTypeId = $mc ? (int) $mc->class_type_id : null;
            $markConfig = $classTypeId
                ? MarkConfig::with('template')
                    ->where('class_type_id', $classTypeId)
                    ->where('term_id', $termId)
                    ->where('school_year', $year)
                    ->first()
                : null;

            if ($markConfig && $markConfig->template) {
                $slots = $markConfig->slotsForDisplay();
            } else {
                // Fallback to default template config so the tabulation never crashes.
                $t = new \App\Models\MarkTemplate();
                $t->setRawAttributes(['configuration' => json_encode(\App\Models\MarkTemplate::defaultConfiguration())]);
                $slots = $t->slotsForDisplay();
            }
            $d['slots'] = $slots;

            // Index marks for O(1) lookup in Blade: marks_index[student_id][subject_id] => Mark model.
            $mks = $this->exam->getMark($wh);
            $marksIndex = [];
            foreach ($mks as $mk) {
                $marksIndex[$mk->student_id][$mk->subject_id] = $mk;
            }
            $d['marks_index'] = $marksIndex;

            // Compute student totals/average/position based on marks table (term + school_year).
            $studentStats = [];
            foreach ($st_ids as $stId) {
                $studentStats[$stId] = [
                    'total' => $this->mark->getExamTotalTerm($termId, $stId, $class_id, $year),
                    'ave' => $this->mark->getExamAvgTerm($termId, $stId, $class_id, $section_id, $year),
                    'pos' => $this->mark->getPos($stId, $termId, $class_id, $section_id, $year),
                ];
            }
            $d['student_stats'] = $studentStats;
        }

        return view('pages.support_team.marks.tabulation.index', $d);
    }

    public function print_tabulation($term, $class_id, $section_id)
    {
        $year = $this->resolveActiveYear();
        $isAnnual = is_string($term) && strtolower($term) === 'annual';

        if ($isAnnual) {
            $built = $this->buildAnnualSectionData((int) $class_id, (int) $section_id, $year);
            if ($built === null) {
                return Qs::goWithDanger('marks.tabulation', __('msg.srnf'));
            }

            $sub_ids = $built['sub_ids'];
            $st_ids = $built['st_ids'];
            $marksTerm1 = $built['marks_term1'];
            $marksTerm2 = $built['marks_term2'];
            $annualStats = $built['annual_stats'];

            $d['my_class'] = $mc = $this->my_class->find($class_id);
            $d['section'] = $this->my_class->findSection($section_id);
            $d['students'] = $this->student->getRecordByUserIDs($st_ids)->get()->sortBy('user.name');

            // Align subject list with web roster (class mapping first).
            $d['subjects'] = $this->my_class->findSubjectByClass($class_id);
            if (! $d['subjects'] || $d['subjects']->count() < 1) {
                $d['subjects'] = $this->my_class->getSubjectsByIDs($sub_ids);
            }

            $d['my_class_id'] = $class_id;
            $d['term'] = 'annual';
            $d['year'] = $year;
            $d['section_id'] = $section_id;

            $d['marks_term1'] = $marksTerm1;
            $d['marks_term2'] = $marksTerm2;

            $d['annual_stats'] = $annualStats;
            $d['rank_term1'] = $built['rank_term1'];
            $d['rank_term2'] = $built['rank_term2'];
            $d['rank_annual'] = $built['rank_annual'];

            $rosterRows = [];
            foreach ($d['students'] as $st) {
                $stId = $st->user_id;
                $row = [
                    'student_id' => $stId,
                    'name' => $st->user->name,
                    'sex' => $this->rosterSexShort($st->user),
                    'adm_no' => ($st->adm_no !== null && $st->adm_no !== '') ? (string) $st->adm_no : '-',
                    'sem1' => [],
                    'sem2' => [],
                    'avg' => [],
                    'term1_total' => $annualStats[$stId]['term1_total'] ?? null,
                    'term2_total' => $annualStats[$stId]['term2_total'] ?? null,
                    'term1_avg' => $annualStats[$stId]['term1_avg'] ?? null,
                    'term2_avg' => $annualStats[$stId]['term2_avg'] ?? null,
                    'annual_avg' => $annualStats[$stId]['annual_avg'] ?? null,
                    'rank_term1' => $annualStats[$stId]['rank_term1'] ?? null,
                    'rank_term2' => $annualStats[$stId]['rank_term2'] ?? null,
                    'rank' => $annualStats[$stId]['rank'] ?? null,
                ];
                foreach ($d['subjects'] as $sub) {
                    $sid = $sub->id;
                    $t1 = $marksTerm1[$stId][$sid] ?? null;
                    $t2 = $marksTerm2[$stId][$sid] ?? null;
                    $a = ($t1 !== null && $t2 !== null) ? round(((float) $t1 + (float) $t2) / 2, 1) : null;
                    $row['sem1'][$sid] = $t1;
                    $row['sem2'][$sid] = $t2;
                    $row['avg'][$sid] = $a;
                }
                $rosterRows[] = $row;
            }
            $d['roster_rows'] = $rosterRows;
            $d['s'] = Setting::all()->flatMap(function ($s) {
                return [$s->type => $s->description];
            });
            $d['s'] = $d['s'] ?? [];

            return view('pages.support_team.marks.tabulation.annual_print', $d);
        }

        $termId = (int) $term;
        $wh = ['my_class_id' => $class_id, 'section_id' => $section_id, 'term' => $termId, 'year' => $year];

        $sub_ids = $this->mark->getSubjectIDs($wh);
        $st_ids = $this->mark->getStudentIDs($wh);

        if (count($sub_ids) < 1 || count($st_ids) < 1) {
            return Qs::goWithDanger('marks.tabulation', __('msg.srnf'));
        }

        $d['subjects'] = $this->my_class->getSubjectsByIDs($sub_ids);
        $d['students'] = $this->student->getRecordByUserIDs($st_ids)->get()->sortBy('user.name');
        $d['my_class_id'] = $class_id;
        $d['term'] = $termId;
        $d['year'] = $year;
        $d['my_class'] = $mc = $this->my_class->find($class_id);
        $d['section'] = $this->my_class->findSection($section_id);

        // Blueprint slots (grading scheme components) for this level + term.
        $classTypeId = $mc ? (int) $mc->class_type_id : null;
        $markConfig = $classTypeId
            ? MarkConfig::with('template')
                ->where('class_type_id', $classTypeId)
                ->where('term_id', $termId)
                ->where('school_year', $year)
                ->first()
            : null;

        if ($markConfig && $markConfig->template) {
            $slots = $markConfig->slotsForDisplay();
        } else {
            $t = new \App\Models\MarkTemplate();
            $t->setRawAttributes(['configuration' => json_encode(\App\Models\MarkTemplate::defaultConfiguration())]);
            $slots = $t->slotsForDisplay();
        }
        $d['slots'] = $slots;

        $mks = $this->exam->getMark($wh);
        $marksIndex = [];
        foreach ($mks as $mk) {
            $marksIndex[$mk->student_id][$mk->subject_id] = $mk;
        }
        $d['marks_index'] = $marksIndex;

        $studentStats = [];
        foreach ($st_ids as $stId) {
            $studentStats[$stId] = [
                'total' => $this->mark->getExamTotalTerm($termId, $stId, $class_id, $year),
                'ave' => $this->mark->getExamAvgTerm($termId, $stId, $class_id, $section_id, $year),
                'pos' => $this->mark->getPos($stId, $termId, $class_id, $section_id, $year),
            ];
        }
        $d['student_stats'] = $studentStats;

        $d['s'] = Setting::all()->flatMap(function ($s) {
            return [$s->type => $s->description];
        });

        return view('pages.support_team.marks.tabulation.print', $d);
    }

    public function tabulation_select(Request $req)
    {
        // Jigjiga roster flow: selector is class + section only, always annual aggregate.
        return redirect()->route('marks.tabulation', ['annual', $req->my_class_id, $req->section_id]);
    }

    protected function verifyStudentExamYear($student_id, $year = null)
    {
        $years = $this->exam->getExamYears($student_id);
        $student_exists = $this->student->exists($student_id);

        if(!$year){
            if($student_exists && $years->count() > 0)
            {
                $d =['years' => $years, 'student_id' => Qs::hash($student_id)];

                return view('pages.support_team.marks.select_year', $d);
            }

            return $this->noStudentRecord();
        }

        return ($student_exists && $years->contains('year', $year)) ? true  : false;
    }

    protected function noStudentRecord()
    {
        return redirect()->route('dashboard')->with('flash_danger', __('msg.srnf'));
    }

    protected function checkPinVerified($st_id)
    {
        return Session::has('pin_verified') && Session::get('pin_verified') == $st_id;
    }

    /**
     * Short sex label for roster columns (M / F).
     */
    protected function rosterSexShort($user): string
    {
        if (! $user || empty($user->gender)) {
            return '-';
        }
        $g = (string) $user->gender;
        if (stripos($g, 'female') !== false) {
            return 'F';
        }
        if (stripos($g, 'male') !== false) {
            return 'M';
        }
        $c = strtoupper(substr(trim($g), 0, 1));

        return $c === 'M' || $c === 'F' ? $c : '-';
    }

    /**
     * Map a subject yearly average (0–100) to the school's grade scale (supports scores below 1 via direct band lookup).
     */
    protected function resolveGradeForSubjectAverage($avg, int $classTypeId): ?Grade
    {
        if ($avg === null || ! is_numeric($avg)) {
            return null;
        }
        $t = round((float) $avg, 1);
        if ($t >= 1) {
            return $this->mark->getGrade($t, $classTypeId);
        }

        $grades = Grade::where(function ($q) use ($classTypeId) {
            $q->where('class_type_id', $classTypeId)->orWhereNull('class_type_id');
        })->orderBy('mark_from')->get();

        foreach ($grades as $g) {
            if ($t >= (float) $g->mark_from && $t <= (float) $g->mark_to) {
                return $g;
            }
        }

        return null;
    }

    /**
     * Shared annual (T1+T2) metrics for a class section: marks matrices, per-student totals/averages, triple ranks.
     *
     * @return array{sub_ids:\Illuminate\Support\Collection,st_ids:\Illuminate\Support\Collection,marks_term1:array,marks_term2:array,annual_stats:array,rank_term1:array,rank_term2:array,rank_annual:array}|null
     */
    protected function buildAnnualSectionData(int $class_id, int $section_id, string $year): ?array
    {
        $term1Id = 1;
        $term2Id = 2;

        $wh1 = ['my_class_id' => $class_id, 'section_id' => $section_id, 'term' => $term1Id, 'year' => $year];
        $wh2 = ['my_class_id' => $class_id, 'section_id' => $section_id, 'term' => $term2Id, 'year' => $year];

        $sub_ids = $this->mark->getSubjectIDs($wh1)
            ->merge($this->mark->getSubjectIDs($wh2))
            ->unique()
            ->values();
        $st_ids = $this->mark->getStudentIDs($wh1)
            ->merge($this->mark->getStudentIDs($wh2))
            ->unique()
            ->values();

        if ($sub_ids->count() < 1 || $st_ids->count() < 1) {
            return null;
        }

        $mksTerm1 = $this->exam->getMark($wh1);
        $mksTerm2 = $this->exam->getMark($wh2);

        $marks_term1 = [];
        foreach ($mksTerm1 as $mk) {
            $marks_term1[$mk->student_id][$mk->subject_id] = $mk->tex1 ?? null;
        }
        $marks_term2 = [];
        foreach ($mksTerm2 as $mk) {
            $marks_term2[$mk->student_id][$mk->subject_id] = $mk->tex2 ?? null;
        }

        $annual_stats = [];
        foreach ($st_ids as $stId) {
            $hasTerm1 = \App\Models\Mark::where([
                'student_id' => $stId,
                'term' => $term1Id,
                'my_class_id' => $class_id,
                'year' => $year,
            ])->whereNotNull('tex1')->exists();

            $hasTerm2 = \App\Models\Mark::where([
                'student_id' => $stId,
                'term' => $term2Id,
                'my_class_id' => $class_id,
                'year' => $year,
            ])->whereNotNull('tex2')->exists();

            $term1Total = $hasTerm1 ? (int) $this->mark->getExamTotalTerm($term1Id, $stId, $class_id, $year) : null;
            $term2Total = $hasTerm2 ? (int) $this->mark->getExamTotalTerm($term2Id, $stId, $class_id, $year) : null;

            $term1Avg = $hasTerm1 ? $this->mark->getExamAvgTerm($term1Id, $stId, $class_id, $section_id, $year) : null;
            $term2Avg = $hasTerm2 ? $this->mark->getExamAvgTerm($term2Id, $stId, $class_id, $section_id, $year) : null;

            $hasAny = $hasTerm1 || $hasTerm2;
            $annualAvg = $hasAny ? round(((float) ($term1Total ?? 0) + (float) ($term2Total ?? 0)) / 2, 1) : null;

            $annual_stats[$stId] = [
                'term1_total' => $term1Total,
                'term2_total' => $term2Total,
                'term1_avg' => $term1Avg,
                'term2_avg' => $term2Avg,
                'annual_avg' => $annualAvg,
                'rank_term1' => null,
                'rank_term2' => null,
                'rank' => null,
            ];
        }

        $rank_term1 = $this->denseRanksDescending($st_ids, function ($id) use ($annual_stats) {
            return $annual_stats[$id]['term1_total'] ?? null;
        });
        $rank_term2 = $this->denseRanksDescending($st_ids, function ($id) use ($annual_stats) {
            return $annual_stats[$id]['term2_total'] ?? null;
        });
        $rank_annual = $this->denseRanksDescending($st_ids, function ($id) use ($annual_stats) {
            return $annual_stats[$id]['annual_avg'] ?? null;
        });

        foreach ($st_ids as $stId) {
            $annual_stats[$stId]['rank_term1'] = $rank_term1[$stId] ?? null;
            $annual_stats[$stId]['rank_term2'] = $rank_term2[$stId] ?? null;
            $annual_stats[$stId]['rank'] = $rank_annual[$stId] ?? null;
        }

        return [
            'sub_ids' => $sub_ids,
            'st_ids' => $st_ids,
            'marks_term1' => $marks_term1,
            'marks_term2' => $marks_term2,
            'annual_stats' => $annual_stats,
            'rank_term1' => $rank_term1,
            'rank_term2' => $rank_term2,
            'rank_annual' => $rank_annual,
        ];
    }

    /**
     * Dense ranking by numeric score descending (higher is better).
     * Students with a null score receive rank null.
     *
     * @param  array|\Illuminate\Support\Collection  $studentIds
     * @param  callable(int $studentId): float|int|null  $scoreGetter
     * @return array<int, int|null>
     */
    protected function denseRanksDescending($studentIds, callable $scoreGetter): array
    {
        $ids = is_array($studentIds) ? $studentIds : $studentIds->all();
        $list = [];
        foreach ($ids as $stId) {
            $score = $scoreGetter($stId);
            $list[] = [
                'stId' => $stId,
                'score' => $score === null ? -INF : (float) $score,
                'has' => $score !== null,
            ];
        }

        usort($list, function ($a, $b) {
            if ($a['score'] === $b['score']) {
                return 0;
            }

            return ($a['score'] < $b['score']) ? 1 : -1;
        });

        $ranks = [];
        $rank = 0;
        $prevScore = null;
        foreach ($list as $item) {
            if (! $item['has']) {
                $ranks[$item['stId']] = null;
                continue;
            }
            if ($prevScore === null || (float) $item['score'] !== (float) $prevScore) {
                $rank++;
                $prevScore = $item['score'];
            }
            $ranks[$item['stId']] = $rank;
        }

        return $ranks;
    }

    /**
     * Resolve the active academic session/year for marks operations.
     *
     * Strict enforcement:
     * - Never allow a null/empty year to reach Mark::create()/firstOrCreate().
     * - Prefer the tenant setting 'current_session', but fall back to a sane default.
     */
    protected function resolveActiveYear(): string
    {
        if (!empty($this->year)) {
            return $this->year;
        }

        // Primary source: per-tenant setting.
        $year = Qs::getSetting('current_session');

        // Hard fallback: computed current session (e.g. "2025-2026").
        if (empty($year)) {
            $year = Qs::getCurrentSession();
        }

        // Final guard: never allow null/empty to leak into DB inserts.
        $year = trim((string) $year);
        if ($year === '') {
            // Log and fail fast so we don't hit SQL integrity errors with a null year.
            Log::error('Active academic session (year) is empty; cannot manage marks.');
            abort(500, 'Active academic session is not configured. Please set the current academic session in Settings.');
        }

        $this->year = $year;
        return $this->year;
    }

}
