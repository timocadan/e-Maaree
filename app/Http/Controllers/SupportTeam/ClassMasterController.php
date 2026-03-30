<?php

namespace App\Http\Controllers\SupportTeam;

use App\Helpers\Qs;
use App\Http\Controllers\Controller;
use App\Models\MyClass;
use App\Models\Setting;
use App\Models\Section;
use App\Models\MarkConfig;
use App\Repositories\ExamRepo;
use App\Repositories\MarkRepo;
use App\Repositories\MyClassRepo;
use App\Repositories\StudentRepo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClassMasterController extends Controller
{
    protected $my_class, $exam, $mark, $student;

    public function __construct(MyClassRepo $my_class, ExamRepo $exam, MarkRepo $mark, StudentRepo $student)
    {
        $this->my_class = $my_class;
        $this->exam = $exam;
        $this->mark = $mark;
        $this->student = $student;
    }

    public function index(Request $req)
    {
        $userId = Auth::id();
        $assignedSections = Section::with('my_class')->where('teacher_id', $userId)->get();
        if ($assignedSections->isEmpty()) {
            return view('pages.support_team.class_master.dashboard', [
                'form_master_classes' => collect(),
                'my_class' => null,
                'sections' => collect(),
                'students' => collect(),
            'term' => null,
                'class_id' => null,
                'section_id' => null,
                'year' => Qs::getSetting('current_session'),
                'grand_totals' => [],
            ])->with('pop_error', __('You are not assigned as Form Master to any section.'));
        }

        $year = Qs::getSetting('current_session');
        $formMasterClasses = $assignedSections->pluck('my_class')->filter()->unique('id')->values();
        $class_id = (int) $req->get('class_id', $formMasterClasses->first()->id);
        $section_id = $req->get('section_id');

        $my_class = MyClass::find($class_id);
        if (!$my_class || !$formMasterClasses->contains('id', (int) $class_id)) {
            $my_class = $formMasterClasses->first();
            $class_id = $my_class->id;
        }

        $sections = $assignedSections->where('my_class_id', (int) $class_id)->values();
        if ($section_id && $sections->contains('id', $section_id)) {
            // keep section_id
        } else {
            $section_id = $sections->first() ? $sections->first()->id : null;
        }

        $students = collect();
        $subjects = collect();
        $roster_rows = [];
        $annual_stats = [];
        $rank_term1 = [];
        $rank_term2 = [];
        $rank_annual = [];
        $tabulation_published = false;
        $mark_config = null;
        if ($class_id && $section_id) {
            $students = $this->student->getRecord([
                'my_class_id' => $class_id,
                'section_id' => $section_id,
                'session' => $year,
            ])->get()->sortBy('user.name');
            $subjects = $this->my_class->findSubjectByClass($class_id);
            $subjectIds = $subjects->pluck('id')->values()->all();
            $studentIds = $students->pluck('user_id')->values()->all();

            $marksTerm1Rows = $this->exam->getMark([
                'term' => 1,
                'my_class_id' => $class_id,
                'section_id' => $section_id,
                'year' => $year,
            ]);
            $marksTerm2Rows = $this->exam->getMark([
                'term' => 2,
                'my_class_id' => $class_id,
                'section_id' => $section_id,
                'year' => $year,
            ]);
            $marks_term1 = [];
            foreach ($marksTerm1Rows as $mk) {
                $marks_term1[$mk->student_id][$mk->subject_id] = $mk->tex1 ?? null;
            }
            $marks_term2 = [];
            foreach ($marksTerm2Rows as $mk) {
                $marks_term2[$mk->student_id][$mk->subject_id] = $mk->tex2 ?? null;
            }

            foreach ($studentIds as $studentId) {
                $term1Total = $this->mark->getExamTotalTerm(1, $studentId, $class_id, $year);
                $term2Total = $this->mark->getExamTotalTerm(2, $studentId, $class_id, $year);
                $term1Avg = $this->mark->getExamAvgTerm(1, $studentId, $class_id, $section_id, $year);
                $term2Avg = $this->mark->getExamAvgTerm(2, $studentId, $class_id, $section_id, $year);

                $hasTerm1 = false;
                $hasTerm2 = false;
                foreach ($subjectIds as $subjectId) {
                    if (($marks_term1[$studentId][$subjectId] ?? null) !== null) {
                        $hasTerm1 = true;
                    }
                    if (($marks_term2[$studentId][$subjectId] ?? null) !== null) {
                        $hasTerm2 = true;
                    }
                }

                $annualAvg = ($hasTerm1 || $hasTerm2)
                    ? round(((float) ($term1Total ?? 0) + (float) ($term2Total ?? 0)) / 2, 1)
                    : null;

                $annual_stats[$studentId] = [
                    'term1_total' => $hasTerm1 ? (int) $term1Total : null,
                    'term2_total' => $hasTerm2 ? (int) $term2Total : null,
                    'term1_avg' => $hasTerm1 ? $term1Avg : null,
                    'term2_avg' => $hasTerm2 ? $term2Avg : null,
                    'annual_avg' => $annualAvg,
                ];
            }

            $rank_term1 = $this->denseRanksDescending($studentIds, function ($id) use ($annual_stats) {
                return $annual_stats[$id]['term1_total'] ?? null;
            });
            $rank_term2 = $this->denseRanksDescending($studentIds, function ($id) use ($annual_stats) {
                return $annual_stats[$id]['term2_total'] ?? null;
            });
            $rank_annual = $this->denseRanksDescending($studentIds, function ($id) use ($annual_stats) {
                return $annual_stats[$id]['annual_avg'] ?? null;
            });

            foreach ($students as $st) {
                $stId = $st->user_id;
                $row = [
                    'student_id' => $stId,
                    'name' => $st->user->name ?? '-',
                    'sex' => $this->rosterSexShort($st->user),
                    'adm_no' => ($st->adm_no !== null && $st->adm_no !== '') ? (string) $st->adm_no : '-',
                    'sem1' => [],
                    'sem2' => [],
                    'avg' => [],
                    'term1_total' => $annual_stats[$stId]['term1_total'] ?? null,
                    'term2_total' => $annual_stats[$stId]['term2_total'] ?? null,
                    'term1_avg' => $annual_stats[$stId]['term1_avg'] ?? null,
                    'term2_avg' => $annual_stats[$stId]['term2_avg'] ?? null,
                    'annual_avg' => $annual_stats[$stId]['annual_avg'] ?? null,
                    'rank_term1' => $rank_term1[$stId] ?? null,
                    'rank_term2' => $rank_term2[$stId] ?? null,
                    'rank' => $rank_annual[$stId] ?? null,
                ];
                foreach ($subjects as $sub) {
                    $sid = $sub->id;
                    $t1 = $marks_term1[$stId][$sid] ?? null;
                    $t2 = $marks_term2[$stId][$sid] ?? null;
                    $row['sem1'][$sid] = $t1;
                    $row['sem2'][$sid] = $t2;
                    $row['avg'][$sid] = ($t1 !== null && $t2 !== null) ? round(((float) $t1 + (float) $t2) / 2, 1) : null;
                }
                $roster_rows[] = $row;
            }

            // Blueprint context for this selected term/year (term_id + school_year).
            $classTypeId = (int) ($my_class->class_type_id ?? 0);
            $mark_config = $classTypeId > 0
                ? MarkConfig::with('template')
                    ->where('class_type_id', $classTypeId)
                    ->where('term_id', 1)
                    ->where('school_year', $year)
                    ->first()
                : null;

            $tabulation_published = $this->isTabulationPublished($year, (int) $class_id, (int) $section_id);
        }

        return view('pages.support_team.class_master.dashboard', [
            'form_master_classes' => $formMasterClasses,
            'my_class' => $my_class,
            'sections' => $sections,
            'students' => $students,
            'subjects' => $subjects,
            'class_id' => $class_id,
            'section_id' => $section_id,
            'year' => $year,
            'roster_rows' => $roster_rows,
            'annual_stats' => $annual_stats,
            'rank_term1' => $rank_term1,
            'rank_term2' => $rank_term2,
            'rank_annual' => $rank_annual,
            'mark_config' => $mark_config,
            'tabulation_published' => $tabulation_published,
        ]);
    }

    public function generateRanks(Request $req)
    {
        $userId = Auth::id();
        $class_id = (int) $req->class_id;
        $section_id = (int) $req->section_id;
        $term = (int) $req->term;
        $section = Section::find($section_id);
        if (!$section || (int) $section->teacher_id !== $userId || (int) $section->my_class_id !== $class_id) {
            return back()->with('pop_error', __('msg.denied'));
        }
        if (!in_array($term, [1, 2], true)) {
            return back()->with('pop_error', __('Invalid term.'));
        }
        $year = Qs::getSetting('current_session');
        $this->mark->updateClassPositions($term, $class_id, $section_id, $year);
        return redirect()->route('class_master.dashboard', ['class_id' => $class_id, 'section_id' => $section_id, 'term' => $term])
            ->with('flash_success', __('Class ranks generated by grand total.'));
    }

    public function printFinalizedRoster(int $class_id, int $section_id)
    {
        $userId = (int) Auth::id();
        $section = Section::with('my_class')->findOrFail($section_id);
        if ((int) $section->teacher_id !== $userId || (int) $section->my_class_id !== (int) $class_id) {
            return redirect()->route('class_master.dashboard')->with('pop_error', __('msg.denied'));
        }

        $year = (string) Qs::getSetting('current_session');
        if (! $this->isTabulationPublished($year, $class_id, $section_id)) {
            return redirect()->route('class_master.dashboard', ['class_id' => $class_id, 'section_id' => $section_id])
                ->with('pop_error', __('Please finalize and publish the roster before printing.'));
        }
        return redirect()->route('marks.print_tabulation', ['annual', $class_id, $section_id]);
    }

    protected function isTabulationPublished(string $year, int $classId, int $sectionId): bool
    {
        $row = Setting::where('type', 'tabulation_publish')->first();
        if (! $row || $row->description === null || $row->description === '') {
            return false;
        }
        $map = json_decode($row->description, true);
        if (! is_array($map)) {
            return false;
        }

        return ! empty($map[$year.'|'.$classId.'|'.$sectionId]);
    }

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
        return in_array($c, ['M', 'F'], true) ? $c : '-';
    }

    protected function denseRanksDescending(array $studentIds, callable $scoreGetter): array
    {
        $items = [];
        foreach ($studentIds as $studentId) {
            $score = $scoreGetter($studentId);
            $items[] = [
                'student_id' => $studentId,
                'score' => $score === null ? null : (float) $score,
            ];
        }

        usort($items, function ($a, $b) {
            $aNull = $a['score'] === null;
            $bNull = $b['score'] === null;
            if ($aNull && $bNull) {
                return 0;
            }
            if ($aNull) {
                return 1;
            }
            if ($bNull) {
                return -1;
            }
            if ((float) $a['score'] === (float) $b['score']) {
                return 0;
            }

            return ((float) $a['score'] < (float) $b['score']) ? 1 : -1;
        });

        $ranks = [];
        $rank = 0;
        $prev = null;
        foreach ($items as $item) {
            if ($item['score'] === null) {
                $ranks[$item['student_id']] = null;
                continue;
            }
            if ($prev === null || (float) $item['score'] !== (float) $prev) {
                $rank++;
                $prev = $item['score'];
            }
            $ranks[$item['student_id']] = $rank;
        }

        return $ranks;
    }
}
