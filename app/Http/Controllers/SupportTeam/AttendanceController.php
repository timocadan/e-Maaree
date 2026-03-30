<?php

namespace App\Http\Controllers\SupportTeam;

use App\Helpers\Qs;
use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Repositories\MyClassRepo;
use App\Repositories\StudentRepo;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    protected $my_class, $student;

    public function __construct(MyClassRepo $my_class, StudentRepo $student)
    {
        $this->my_class = $my_class;
        $this->student = $student;
    }

    public function index()
    {
        $d = $this->selectionData();
        $d['selected'] = false;

        return view('pages.support_team.attendance.index', $d);
    }

    public function report()
    {
        $d = $this->selectionData();
        $d['report_month'] = now()->format('Y-m');

        return view('pages.support_team.attendance.report', $d);
    }

    public function showReport(Request $req)
    {
        $validated = $req->validate([
            'my_class_id' => 'required|integer|exists:my_classes,id',
            'section_id' => 'required|integer|exists:sections,id',
            'report_month' => 'required|date_format:Y-m',
        ]);

        $section = $this->my_class->findSection($validated['section_id']);
        if (!$section || (int) $section->my_class_id !== (int) $validated['my_class_id']) {
            return back()->with('flash_danger', __('Selected section does not belong to the chosen class.'));
        }

        $monthStart = Carbon::createFromFormat('Y-m', $validated['report_month'])->startOfMonth();
        $monthEnd = $monthStart->copy()->endOfMonth();
        $students = $this->student->getRecord([
            'my_class_id' => (int) $validated['my_class_id'],
            'section_id' => (int) $validated['section_id'],
        ])->get()->sortBy('user.name');

        $attendanceRecords = Attendance::where('my_class_id', (int) $validated['my_class_id'])
            ->where('section_id', (int) $validated['section_id'])
            ->whereBetween('date', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->get()
            ->groupBy('student_id');

        $totalWorkingDays = $this->workingDaysInMonth($monthStart);
        $reportRows = $students->map(function ($student) use ($attendanceRecords, $totalWorkingDays) {
            $records = $attendanceRecords->get($student->user_id, collect());
            $present = (int) $records->where('status', 'present')->count();
            $absent = (int) $records->where('status', 'absent')->count();
            $late = (int) $records->where('status', 'late')->count();
            $excused = (int) $records->where('status', 'excused')->count();

            return [
                'name' => $student->user->name ?? 'Unknown Student',
                'adm_no' => $student->adm_no ?? '—',
                'present' => $present,
                'absent' => $absent,
                'late' => $late,
                'excused' => $excused,
                'attendance_percent' => $totalWorkingDays > 0 ? round(($present / $totalWorkingDays) * 100, 1) : 0,
            ];
        })->values();

        return view('pages.support_team.attendance.report_results', [
            'report_rows' => $reportRows,
            'report_month' => $validated['report_month'],
            'my_class' => $this->my_class->find($validated['my_class_id']),
            'section' => $section,
            'total_working_days' => $totalWorkingDays,
        ]);
    }

    public function showMarkingGrid(Request $req)
    {
        $validated = $req->validate([
            'my_class_id' => 'required|integer|exists:my_classes,id',
            'section_id' => 'required|integer|exists:sections,id',
            'date' => 'required|date|before_or_equal:today',
        ], [
            'date.before_or_equal' => 'You cannot mark attendance for a future date.',
        ]);

        $section = $this->my_class->findSection($validated['section_id']);
        if (!$section || (int) $section->my_class_id !== (int) $validated['my_class_id']) {
            return back()->with('flash_danger', __('Selected section does not belong to the chosen class.'));
        }

        if (Qs::userIsTeacher() && (int) $section->teacher_id !== (int) Auth::id()) {
            return redirect()->route('dashboard')->with('flash_danger', __('msg.denied'));
        }

        $students = $this->student->getRecord([
            'my_class_id' => (int) $validated['my_class_id'],
            'section_id' => (int) $validated['section_id'],
        ])->get()->sortBy('user.name');
        $weekDays = $this->workingWeekDays($validated['date']);
        $attendanceRecords = Attendance::where('my_class_id', (int) $validated['my_class_id'])
            ->where('section_id', (int) $validated['section_id'])
            ->whereIn('date', collect($weekDays)->pluck('date')->all())
            ->get()
            ->groupBy('student_id')
            ->map(function ($records) {
                return $records->keyBy(function (Attendance $attendance) {
                    return $attendance->date instanceof Carbon
                        ? $attendance->date->toDateString()
                        : (string) $attendance->date;
                });
            });

        $d = $this->selectionData();
        $d['selected'] = true;
        $d['students'] = $students;
        $d['attendance_records'] = $attendanceRecords;
        $d['my_class_id'] = (int) $validated['my_class_id'];
        $d['section_id'] = (int) $validated['section_id'];
        $d['attendance_date'] = $validated['date'];
        $d['current_session'] = Qs::getCurrentSession();
        $d['my_class'] = $this->my_class->find($validated['my_class_id']);
        $d['section'] = $section;
        $d['week_days'] = $weekDays;

        return view('pages.support_team.attendance.grid', $d);
    }

    public function store(Request $req)
    {
        $validated = $req->validate([
            'my_class_id' => 'required|integer|exists:my_classes,id',
            'section_id' => 'required|integer|exists:sections,id',
            'date' => 'required|date|before_or_equal:today',
            'attendance' => 'nullable|array',
            'attendance.*' => 'nullable|array',
            'attendance.*.*' => 'nullable|in:present,absent,late,excused',
        ], [
            'date.before_or_equal' => 'You cannot mark attendance for a future date.',
        ]);

        $section = $this->my_class->findSection($validated['section_id']);
        if (!$section || (int) $section->my_class_id !== (int) $validated['my_class_id']) {
            return back()->with('flash_danger', __('Selected section does not belong to the chosen class.'));
        }

        if (Qs::userIsTeacher() && (int) $section->teacher_id !== (int) Auth::id()) {
            return redirect()->route('dashboard')->with('flash_danger', __('msg.denied'));
        }

        $session = Qs::getCurrentSession();
        $studentIds = $this->student->getRecord([
            'my_class_id' => (int) $validated['my_class_id'],
            'section_id' => (int) $validated['section_id'],
        ])->pluck('user_id')->all();
        $weekDays = $this->workingWeekDays($validated['date']);

        $entries = $validated['attendance'] ?? [];
        foreach ($studentIds as $studentId) {
            $studentId = (int) $studentId;
            foreach ($weekDays as $weekDay) {
                $dayDate = $weekDay['date'];
                if (Carbon::parse($dayDate)->gt(Carbon::parse(now()->toDateString()))) {
                    continue;
                }
                $status = $entries[$studentId][$dayDate] ?? 'present';

                Attendance::updateOrCreate(
                    [
                        'student_id' => $studentId,
                        'date' => $dayDate,
                    ],
                    [
                        'my_class_id' => (int) $validated['my_class_id'],
                        'section_id' => (int) $validated['section_id'],
                        'status' => $status,
                        'session' => $session,
                    ]
                );
            }
        }

        return redirect()
            ->route('attendance.show_marking_grid', [
                'my_class_id' => (int) $validated['my_class_id'],
                'section_id' => (int) $validated['section_id'],
                'date' => $validated['date'],
            ])
            ->with('flash_success', __('Attendance saved successfully.'));
    }

    protected function selectionData(): array
    {
        $classes = $this->my_class->all();
        $sections = $this->my_class->getAllSections();

        if (Qs::userIsTeacher()) {
            $teacherId = (int) Auth::id();
            $sections = $sections->where('teacher_id', $teacherId)->values();
            $classes = $classes->whereIn('id', $sections->pluck('my_class_id')->unique()->values())->values();
        }

        return [
            'my_classes' => $classes,
            'sections' => $sections,
        ];
    }

    protected function workingWeekDays(string $date): array
    {
        $selectedDate = Carbon::parse($date)->startOfDay();
        $weekendType = trim((string) Qs::getSetting('weekend_type'));
        if ($weekendType === 'thu_fri') {
            // Working week: Saturday -> Wednesday. If teacher picks Thu/Fri, jump to next Saturday.
            if (in_array($selectedDate->dayOfWeek, [Carbon::THURSDAY, Carbon::FRIDAY], true)) {
                $weekStart = $selectedDate->copy()->next(Carbon::SATURDAY);
            } else {
                $weekStart = $selectedDate->copy()->startOfWeek(Carbon::SATURDAY);
            }
        } else {
            // Working week: Monday -> Friday. If teacher picks Sat/Sun, jump to next Monday.
            if (in_array($selectedDate->dayOfWeek, [Carbon::SATURDAY, Carbon::SUNDAY], true)) {
                $weekStart = $selectedDate->copy()->next(Carbon::MONDAY);
            } else {
                $weekStart = $selectedDate->copy()->startOfWeek(Carbon::MONDAY);
            }
        }

        $days = [];
        for ($i = 0; $i < 5; $i++) {
            $day = $weekStart->copy()->addDays($i);
            $days[] = [
                'date' => $day->toDateString(),
                'label' => $day->format('D (M j)'),
                'day_name' => $day->format('l'),
            ];
        }

        return $days;
    }

    protected function workingDaysInMonth(Carbon $monthStart): int
    {
        $current = $monthStart->copy()->startOfMonth();
        $end = $monthStart->copy()->endOfMonth();
        $weekendDays = $this->weekendDayNumbers();
        $count = 0;

        while ($current->lte($end)) {
            if (!in_array($current->dayOfWeek, $weekendDays, true)) {
                $count++;
            }
            $current->addDay();
        }

        return $count;
    }

    protected function weekendDayNumbers(): array
    {
        $weekendType = trim((string) Qs::getSetting('weekend_type'));

        return $weekendType === 'thu_fri'
            ? [Carbon::THURSDAY, Carbon::FRIDAY]
            : [Carbon::SATURDAY, Carbon::SUNDAY];
    }
}
