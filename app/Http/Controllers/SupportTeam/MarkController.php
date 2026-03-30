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
use App\Models\MyClass;
use App\Models\Mark;
use App\Models\Section;
use App\Models\MarkTemplate;

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

    protected function normalizeMarkValue($value): ?int
    {
        return ($value === '' || $value === null) ? null : (int) $value;
    }

    protected function resolveActiveSchemeId(?MarkConfig $config = null): int
    {
        $storedSchemeId = (int) Qs::getSetting('active_scheme_id');
        if ($storedSchemeId > 0) {
            return $storedSchemeId;
        }

        if ($config && (int) $config->mark_template_id > 0) {
            return (int) $config->mark_template_id;
        }

        $firstConfig = MarkConfig::whereNotNull('mark_template_id')->orderBy('id')->first();
        return $firstConfig ? (int) $firstConfig->mark_template_id : 0;
    }

    protected function resolveActiveAssessmentTitle(?MarkConfig $config, array $slots = []): string
    {
        $storedTitle = trim((string) Qs::getSetting('active_assessment_title'));
        if ($storedTitle !== '') {
            return $storedTitle;
        }

        if (!$config) {
            return '';
        }

        if (empty($slots)) {
            $slots = $config->slotsForDisplay();
        }

        $legacyIndex = (int) ($config->active_slot ?? 0);
        foreach ($slots as $slot) {
            if ((int) ($slot['slot_index'] ?? -1) === $legacyIndex) {
                return trim((string) ($slot['label'] ?? ''));
            }
        }

        return isset($slots[0]) ? trim((string) ($slots[0]['label'] ?? '')) : '';
    }

    protected function editableSlotKeys(array $slots, string $activeAssessmentTitle): array
    {
        $activeAssessmentTitle = trim($activeAssessmentTitle);
        if ($activeAssessmentTitle === '') {
            return [];
        }

        return collect($slots)
            ->filter(function (array $slot) use ($activeAssessmentTitle) {
                return trim((string) ($slot['label'] ?? '')) === $activeAssessmentTitle;
            })
            ->pluck('key')
            ->values()
            ->all();
    }

    public function index()
    {
        $d['terms'] = [1 => __('Term 1'), 2 => __('Term 2')];
        $d['my_classes'] = $this->my_class->all();
        $d['sections'] = $this->my_class->getAllSections();
        $d['subjects'] = $this->my_class->getAllSubjects();
        if (Qs::userIsTeacher()) {
            $teacherId = (int) Auth::id();
            $d['sections'] = $d['sections']->where('teacher_id', $teacherId)->values();
            $d['my_classes'] = $d['my_classes']->whereIn('id', $d['sections']->pluck('my_class_id')->unique()->values())->values();
            $d['subjects'] = $this->my_class->findSubjectByTeacher($teacherId);
        }
        $d['selected'] = false;

        return view('pages.support_team.marks.index', $d);
    }

    public function year_selector($student_id = null)
    {
        $student_id = $this->resolveRequestedStudentId($student_id);
        return $this->verifyStudentExamYear($student_id);
    }

    public function year_selected(Request $req, $student_id = null)
    {
        $student_id = $this->resolveRequestedStudentId($student_id);
        if(!$this->verifyStudentExamYear($student_id, $req->year)){
            return $this->noStudentRecord();
        }

        $student_id = Qs::hash($student_id);
        return redirect()->route('marks.show', [$student_id, $req->year]);
    }

    public function show($student_id = null, $year = null)
    {
        $student_id = $this->resolveRequestedStudentId($student_id);
        if ($year === null || trim((string) $year) === '') {
            abort(404);
        }
        /* Prevent Other Students/Parents from viewing Result of others */
        if(Auth::user()->id != $student_id && !Qs::userIsTeamSAT() && !Qs::userIsMyChild($student_id, Auth::user()->id)){
            return redirect(route('dashboard'))->with('pop_error', __('msg.denied'));
        }

        if(!$this->verifyStudentExamYear($student_id, $year)){
            return $this->noStudentRecord();
        }

        $wh = ['student_id' => $student_id, 'year' => $year];
        $d['marks'] = $this->exam->getMark($wh);
        if (method_exists($d['marks'], 'load')) {
            $d['marks']->load('grade');
        }
        $d['exam_records'] = $exr = $this->exam->getRecord($wh);
        $d['terms'] = [1 => __('Term 1'), 2 => __('Term 2')];
        $d['sr'] = $this->student->getRecord(['user_id' => $student_id])->first();
        $d['my_class'] = $mc = $this->my_class->getMC(['id' => $exr->first()->my_class_id])->first();
        $d['class_type'] = $this->my_class->findTypeByClass($mc->id);
        $d['subjects'] = $this->my_class->findSubjectByClass($mc->id);
        $d['year'] = $year;
        $d['student_id'] = $student_id;

        // Re-evaluate grades dynamically against the CURRENT grade scheme for this level.
        // Also self-heal old rows: if grade is null/"N/A", sync the stored grade_id.
        $classTypeId = (int) ($d['class_type']->id ?? 0);
        $computedGradesByMarkId = [];
        if ($classTypeId > 0) {
            foreach ($d['marks'] as $mk) {
                $termNum = (int) ($mk->term ?? 0);
                $texField = 'tex' . $termNum;
                $score = isset($mk->$texField) && is_numeric($mk->$texField) ? (float) $mk->$texField : null;

                $computedGrade = $score !== null ? $this->mark->getGrade($score, $classTypeId) : null;
                $computedGradesByMarkId[$mk->id] = $computedGrade ? $computedGrade->name : 'N/A';

                $storedIsMissingOrNA = !$mk->grade || strtoupper(trim((string) optional($mk->grade)->name)) === 'N/A';
                if ($storedIsMissingOrNA && $computedGrade && (int) ($mk->grade_id ?? 0) !== (int) $computedGrade->id) {
                    $this->exam->updateMark($mk->id, ['grade_id' => $computedGrade->id]);
                    $mk->grade_id = $computedGrade->id;
                    $mk->setRelation('grade', $computedGrade);
                }
            }
        }
        $d['computed_grade_by_mark_id'] = $computedGradesByMarkId;

        // Blueprint-driven assessment titles (slots) + term grades for Masterpiece web marksheet.
        $slotMap = [];
        $termGradeMap = [];
        foreach ([1, 2] as $t) {
            $cfg = $classTypeId > 0
                ? MarkConfig::with('template')
                    ->where('class_type_id', $classTypeId)
                    ->where('term_id', (int) $t)
                    ->where('school_year', $year)
                    ->first()
                : null;

            $slotMap[$t] = ($cfg && $cfg->template)
                ? $cfg->slotsForDisplay()
                : (new MarkTemplate())->slotsForDisplay();

            $termExr = $exr->firstWhere('term', (int) $t);
            $avg = $termExr && isset($termExr->ave) && is_numeric($termExr->ave) ? (float) $termExr->ave : null;
            $termGradeMap[$t] = ($avg !== null && $classTypeId > 0) ? $this->mark->getGrade($avg, $classTypeId) : null;
        }
        $d['mark_slots_by_term'] = $slotMap;
        $d['term_grade_by_term'] = $termGradeMap;
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
        $sr = $this->student->getRecord(['user_id' => $student_id])->first();
        /* Prevent Other Students/Parents from viewing Result of others */
        if(Auth::user()->id != $student_id && !Qs::userIsTeamSA() && !Qs::userIsMyChild($student_id, Auth::user()->id) && (!$sr || !$this->teacherCanAccessSection((int) $sr->section_id, (int) $sr->my_class_id))){
            return redirect(route('dashboard'))->with('pop_error', __('msg.denied'));
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

        $d['draft_watermark'] = ! $this->isTabulationPublished($year, (int) $mc->id, (int) $exr->section_id);

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

        $sr = $this->student->getRecord(['user_id' => $student_id])->first();
        if (Auth::user()->id != $student_id && ! Qs::userIsTeamSA() && ! Qs::userIsMyChild($student_id, Auth::user()->id) && (!$sr || !$this->teacherCanAccessSection((int) $sr->section_id, (int) $sr->my_class_id))) {
            return redirect(route('dashboard'))->with('pop_error', __('msg.denied'));
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

        // Single-student roster matrix (same payload shape as tabulation/annual_print roster rows).
        $roster_row = [
            'name' => $sr->user->name,
            'sex' => $this->rosterSexShort($sr->user),
            'adm_no' => ($sr->adm_no !== null && $sr->adm_no !== '') ? (string) $sr->adm_no : '-',
            'sem1' => [],
            'sem2' => [],
            'avg' => [],
            'term1_total' => $my['term1_total'] ?? null,
            'term2_total' => $my['term2_total'] ?? null,
            'term1_avg' => $my['term1_avg'] ?? null,
            'term2_avg' => $my['term2_avg'] ?? null,
            'annual_avg' => $my['annual_avg'] ?? null,
            'rank_term1' => $my['rank_term1'] ?? null,
            'rank_term2' => $my['rank_term2'] ?? null,
            'rank' => $my['rank'] ?? null,
        ];
        foreach ($subjects as $sub) {
            $sid = $sub->id;
            $t1 = $marks_term1[$student_id][$sid] ?? null;
            $t2 = $marks_term2[$student_id][$sid] ?? null;
            $a = ($t1 !== null && $t2 !== null) ? round(((float) $t1 + (float) $t2) / 2, 1) : null;
            $roster_row['sem1'][$sid] = $t1;
            $roster_row['sem2'][$sid] = $t2;
            $roster_row['avg'][$sid] = $a;
        }

        $class_type = $this->my_class->findTypeByClass($class_id);

        $d['sr'] = $sr;
        $d['my_class'] = $this->my_class->find($class_id);
        if ($d['my_class']) {
            $d['my_class']->loadMissing('teacher');
        }
        $d['class_type'] = $class_type;
        $d['year'] = $year;
        $d['student_id'] = $student_id;
        $d['subjects'] = $subjects;
        $d['roster_row'] = $roster_row;
        $d['section'] = $this->my_class->findSection($section_id);

        $d['s'] = Setting::all()->flatMap(function ($s) {
            return [$s->type => $s->description];
        });

        $d['draft_watermark'] = ! $this->isTabulationPublished($year, $class_id, $section_id);

        return view('pages.support_team.marks.print.annual', $d);
    }

    public function tabulation_publish(Request $request)
    {
        if (! Qs::userIsTeamSA()) {
            return redirect(route('dashboard'))->with('pop_error', __('msg.denied'));
        }

        $year = $this->resolveActiveYear();
        $class_id = (int) $request->input('my_class_id');
        $section_id = (int) $request->input('section_id');
        $on = $request->boolean('finalize_publish');
        $this->setTabulationPublished($year, $class_id, $section_id, $on);

        return redirect()->route('marks.tabulation', ['term' => 'annual', 'class' => $class_id, 'sec_id' => $section_id])
            ->with('flash_success', $on ? __('Results published for printing.') : __('Publish cleared; prints show as draft.'));
    }

    public function selector(MarkSelector $req)
    {
        if (! $this->teacherCanAccessSection((int) $req->section_id, (int) $req->my_class_id)) {
            return redirect()->route('marks.index')->with('pop_error', __('msg.denied'));
        }

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
        if (! $this->teacherCanAccessSection((int) $section_id, (int) $class_id)) {
            return redirect()->route('marks.index')->with('pop_error', __('msg.denied'));
        }

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

        $d['active_scheme_id'] = $this->resolveActiveSchemeId($d['mark_config']);
        $d['active_assessment_title'] = $this->resolveActiveAssessmentTitle($d['mark_config'], $d['mark_config']->slotsForDisplay());
        $d['scheme_is_active'] = (int) $d['mark_config']->mark_template_id === (int) $d['active_scheme_id'];

        return view('pages.support_team.marks.manage', $d);
    }

    public function update(Request $req, $term, $class_id, $section_id, $subject_id)
    {
        if (! $this->teacherCanAccessSection((int) $section_id, (int) $class_id)) {
            return response()->json(['msg' => __('msg.denied')], 403);
        }

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
        $activeSchemeId = $this->resolveActiveSchemeId($config);
        if ((int) $config->mark_template_id !== $activeSchemeId) {
            return response()->json(['msg' => 'This class is locked because the active grading scheme in Settings does not match its blueprint.'], 422);
        }
        $displaySlots = $config->slotsForDisplay();
        $activeAssessmentTitle = $this->resolveActiveAssessmentTitle($config, $displaySlots);
        $editableSlotKeys = $this->editableSlotKeys($displaySlots, $activeAssessmentTitle);
        if (empty($editableSlotKeys)) {
            return response()->json(['msg' => 'No editable assessment matches the active title configured in Settings.'], 422);
        }

        $slotMeta = [];
        foreach ($displaySlots as $s) {
            $slotMeta[$s['key']] = [
                'max' => (int) ($s['max'] ?? 0),
                'label' => trim((string) ($s['label'] ?? $s['key'])),
            ];
        }
        $lockedSlotKeys = array_values(array_diff(array_keys($slotMeta), $editableSlotKeys));

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
            foreach ($slotMeta as $key => $_meta) {
                $d[$key] = $mk->{$key} ?? null;
            }
            $d['tca'] = 0;

            foreach ($lockedSlotKeys as $lockedKey) {
                $lockedInputKey = $lockedKey . '_' . $mk->id;
                if (!array_key_exists($lockedInputKey, $mks)) {
                    continue;
                }

                $submittedLockedValue = $this->normalizeMarkValue($mks[$lockedInputKey]);
                $currentLockedValue = $this->normalizeMarkValue($mk->{$lockedKey} ?? null);
                if ($submittedLockedValue !== $currentLockedValue) {
                    return response()->json(['msg' => ($slotMeta[$lockedKey]['label'] ?? 'This assessment') . ' is currently locked for editing.'], 422);
                }
            }

            foreach ($editableSlotKeys as $editableKey) {
                $inputKey = $editableKey . '_' . $mk->id;
                if (!array_key_exists($inputKey, $mks)) {
                    continue;
                }

                $val = $this->normalizeMarkValue($mks[$inputKey]);
                $maxAllowed = (int) ($slotMeta[$editableKey]['max'] ?? 0);

                if ($val !== null && ($val < 0 || $val > $maxAllowed)) {
                    if ($editableKey === 'exm') {
                        return response()->json(['msg' => __('Exam score must be between 0 and :max', ['max' => $maxAllowed])], 422);
                    }
                    return response()->json(['msg' => __('Score must be between 0 and :max', ['max' => $maxAllowed])], 422);
                }

                $d[$editableKey] = $val;
            }

            $tca = 0;
            foreach ($slotMeta as $key => $_meta) {
                if ($key === 'exm') {
                    continue;
                }
                $v = $d[$key] ?? null;
                if ($v !== null) {
                    $tca += (int) $v;
                }
            }
            $exm = $d['exm'] ?? null;
            $examMax = (int) ($slotMeta['exm']['max'] ?? 0);
            if ($exm !== null && ($exm < 0 || $exm > $examMax)) {
                return response()->json(['msg' => __('Exam score must be between 0 and :max', ['max' => $examMax])], 422);
            }
            $d['tca'] = $tca;
            $total = $tca + (int) $exm;
            $d['tex' . $term] = $total;

            if ($total > $config->totalMax()) {
                $d['tex' . $term] = null;
                foreach ($slotMeta as $key => $_meta) {
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
                $d['has_term2_marks'] = $this->sectionHasTerm2Marks((int) $class_id, (int) $section_id, $year);
                $d['tabulation_published'] = $this->isTabulationPublished($year, (int) $class_id, (int) $section_id);

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
        if (!Qs::userIsTeamSA() && !$this->teacherCanAccessSection((int) $section_id, (int) $class_id)) {
            return redirect(route('dashboard'))->with('pop_error', __('msg.denied'));
        }

        // Support legacy/alternate argument order: {class}/{section}/annual
        // while keeping the canonical route order {term}/{class}/{section}.
        if (is_numeric($term) && is_numeric($class_id) && is_string($section_id) && strtolower($section_id) === 'annual') {
            $actualClassId = (int) $term;
            $actualSectionId = (int) $class_id;
            $term = 'annual';
            $class_id = $actualClassId;
            $section_id = $actualSectionId;
        }

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

            $hasTerm2 = $this->sectionHasTerm2Marks((int) $class_id, (int) $section_id, $year);
            $published = $this->isTabulationPublished($year, (int) $class_id, (int) $section_id);

            $d['my_class'] = $mc = $this->my_class->find($class_id);
            if ($mc) {
                $mc->loadMissing('teacher');
            }
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

            $d['draft_watermark'] = ! $published;
            $d['published'] = $published;

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

    /**
     * Resolve requested student user_id from route.
     * - Students are always bound to their own Auth::id()
     * - If a student tampers with another ID, abort 403
     * - Non-students must provide a valid hashed ID
     */
    protected function resolveRequestedStudentId($student_id = null): int
    {
        $authId = (int) Auth::id();

        if (Qs::userIsStudent()) {
            if ($student_id !== null) {
                $decoded = Qs::decodeHash($student_id);
                if ($decoded !== null && (int) $decoded !== $authId) {
                    abort(403, __('msg.denied'));
                }
            }

            return $authId;
        }

        if ($student_id === null) {
            abort(404);
        }

        $decoded = Qs::decodeHash($student_id);
        if ($decoded === null) {
            abort(404);
        }

        return (int) $decoded;
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

        $subjects = $this->my_class->findSubjectByClass($class_id);
        $sub_ids = $subjects ? $subjects->pluck('id') : collect([]);

        $students = $this->student->getRecord(['my_class_id' => $class_id, 'section_id' => $section_id, 'session' => $year])->get();
        if ($students->count() < 1) {
            $students = $this->student->getRecord(['my_class_id' => $class_id, 'section_id' => $section_id])->get();
        }
        $st_ids = $students->pluck('user_id');

        if ($sub_ids->count() < 1 || $st_ids->count() < 1) {
            return null;
        }

        $mksTerm1 = $this->exam->getMark($wh1);
        $mksTerm2 = $this->exam->getMark($wh2);

        $marks_term1 = [];
        $marks_term2 = [];
        foreach($st_ids as $stId) { $marks_term1[$stId] = []; $marks_term2[$stId] = []; }
        foreach ($mksTerm1 as $mk) {
            $marks_term1[$mk->student_id][$mk->subject_id] = $mk->tex1 ?? null;
        }
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

    /**
     * True if at least one student in the section has Term 2 final marks (tex2) on file.
     */
    protected function sectionHasTerm2Marks(int $class_id, int $section_id, string $year): bool
    {
        return Mark::query()
            ->where([
                'my_class_id' => $class_id,
                'section_id' => $section_id,
                'term' => 2,
                'year' => $year,
            ])
            ->whereNotNull('tex2')
            ->exists();
    }

    protected function tabulationPublishMap(): array
    {
        $row = Setting::where('type', 'tabulation_publish')->first();
        if (! $row || $row->description === null || $row->description === '') {
            return [];
        }
        $decoded = json_decode($row->description, true);

        return is_array($decoded) ? $decoded : [];
    }

    public function isTabulationPublished(string $year, int $classId, int $sectionId): bool
    {
        $key = $year.'|'.$classId.'|'.$sectionId;
        $map = $this->tabulationPublishMap();

        return ! empty($map[$key]);
    }

    protected function setTabulationPublished(string $year, int $classId, int $sectionId, bool $on): void
    {
        $map = $this->tabulationPublishMap();
        $key = $year.'|'.$classId.'|'.$sectionId;
        if ($on) {
            $map[$key] = true;
        } else {
            unset($map[$key]);
        }
        Setting::updateOrCreate(
            ['type' => 'tabulation_publish'],
            ['description' => json_encode($map)]
        );
    }

    protected function teacherCanAccessSection(int $sectionId, ?int $classId = null): bool
    {
        if (! Qs::userIsTeacher()) {
            return true;
        }
        if ($sectionId < 1) {
            return false;
        }

        $section = Section::find($sectionId);
        if (! $section) {
            return false;
        }
        if ((int) $section->teacher_id !== (int) Auth::id()) {
            return false;
        }
        if ($classId !== null && $classId > 0 && (int) $section->my_class_id !== (int) $classId) {
            return false;
        }

        return true;
    }

}
