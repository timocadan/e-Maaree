<?php

namespace App\Http\Controllers;

use App\Helpers\Qs;
use App\Models\Attendance;
use App\Repositories\UserRepo;
use App\Repositories\StudentRepo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    protected $user, $student;
    public function __construct(UserRepo $user, StudentRepo $student)
    {
        $this->user = $user;
        $this->student = $student;
    }


    public function index()
    {
        return redirect()->route('dashboard');
    }

    public function privacy_policy()
    {
        $data['app_name'] = config('app.name');
        $data['app_url'] = config('app.url');
        $data['contact_phone'] = Qs::getSetting('phone');
        return view('pages.other.privacy_policy', $data);
    }

    public function terms_of_use()
    {
        $data['app_name'] = config('app.name');
        $data['app_url'] = config('app.url');
        $data['contact_phone'] = Qs::getSetting('phone');
        return view('pages.other.terms_of_use', $data);
    }

    public function dashboard(Request $request)
    {
        $d = ['users' => collect()];
        if (Qs::userIsTeamSAT()) {
            $d['users'] = $this->user->getAll();
        }

        $d['attendance_overview'] = null;
        $d['attendance_children'] = collect();
        $d['selected_attendance_child_id'] = null;

        if (Qs::userIsStudent()) {
            $d['attendance_overview'] = $this->buildAttendanceOverview(Auth::id());
        }

        if (Qs::userIsParent()) {
            $children = $this->student->getRecord(['my_parent_id' => Auth::id()])
                ->with(['user', 'my_class', 'section'])
                ->get()
                ->sortBy('user.name')
                ->values();

            $selectedChildId = (int) $request->query('child_id');
            $selectedChild = $children->firstWhere('user_id', $selectedChildId) ?: $children->first();

            $d['attendance_children'] = $children;
            $d['selected_attendance_child_id'] = $selectedChild->user_id ?? null;
            $d['attendance_overview'] = $selectedChild ? $this->buildAttendanceOverview((int) $selectedChild->user_id) : null;
        }

        return view('pages.support_team.dashboard', $d);
    }

    protected function buildAttendanceOverview(int $studentId): ?array
    {
        $studentRecord = $this->student->findByUserId($studentId)
            ->with(['user', 'my_class', 'section'])
            ->first();

        if (!$studentRecord) {
            return null;
        }

        $records = Attendance::where('student_id', $studentId)
            ->where('session', Qs::getCurrentSession())
            ->get();

        $present = (int) $records->where('status', 'present')->count();
        $absent = (int) $records->where('status', 'absent')->count();
        $late = (int) $records->where('status', 'late')->count();
        $excused = (int) $records->where('status', 'excused')->count();
        $total = $present + $absent + $late + $excused;
        $percentage = $total > 0 ? round(($present / $total) * 100, 1) : 0;

        if ($percentage > 90) {
            $message = 'Excellent Attendance!';
            $messageClass = 'text-success';
        } elseif ($percentage >= 75) {
            $message = 'Good, keep it up.';
            $messageClass = 'text-warning';
        } else {
            $message = 'Attendance needs improvement.';
            $messageClass = 'text-danger';
        }

        return [
            'student_name' => $studentRecord->user->name ?? 'Student',
            'adm_no' => $studentRecord->adm_no ?? '—',
            'class_name' => $studentRecord->my_class->name ?? '',
            'section_name' => $studentRecord->section->name ?? '',
            'present' => $present,
            'absent' => $absent,
            'late' => $late,
            'excused' => $excused,
            'total' => $total,
            'percentage' => $percentage,
            'message' => $message,
            'message_class' => $messageClass,
        ];
    }
}
