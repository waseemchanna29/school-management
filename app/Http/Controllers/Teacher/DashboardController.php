<?php

namespace App\Http\Controllers\Teacher;

use App\Helpers\AcademicYearContext;
use App\Http\Controllers\Controller;
use App\Models\AttendanceSession;
use App\Models\StudentEnrollment;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $teacher = Auth::user()->teacher;

        if (!$teacher) {
            return redirect()->route('login')
                ->with('error', 'Teacher profile not found.');
        }

        $yearId = AcademicYearContext::id();

        // Section this teacher is class teacher of
        $section = $teacher->classTeacherOf()
            ->with(['schoolClass'])
            ->first();

        // Student count via enrollments (not section->students)
        $studentCount = $section
            ? StudentEnrollment::where('section_id', $section->id)
                ->where('academic_year_id', $yearId)
                ->where('status', 'active')
                ->count()
            : 0;

        // Recent sessions
        $recentSessions = AttendanceSession::where('teacher_id', $teacher->id)
            ->where('academic_year_id', $yearId)
            ->with(['section', 'schoolClass'])
            ->latest('date')
            ->take(7)
            ->get();

        // Today's session
        $todaySession = $section
            ? AttendanceSession::where('teacher_id', $teacher->id)
                ->where('section_id', $section->id)
                ->where('academic_year_id', $yearId)
                ->whereDate('date', today())
                ->withCount([
                    'records as present_count' => fn($q) =>
                        $q->where('status', 'present'),
                    'records as absent_count' => fn($q) =>
                        $q->where('status', 'absent'),
                ])
                ->first()
            : null;

        // This month summary
        $monthStats = [
            'sessions'  => AttendanceSession::where('teacher_id', $teacher->id)
                ->where('academic_year_id', $yearId)
                ->whereMonth('date', date('n'))
                ->whereYear('date',  date('Y'))
                ->count(),
            'submitted' => AttendanceSession::where('teacher_id', $teacher->id)
                ->where('academic_year_id', $yearId)
                ->whereMonth('date', date('n'))
                ->whereYear('date',  date('Y'))
                ->where('status', 'submitted')
                ->count(),
        ];

        return view('teacher.dashboard', compact(
            'teacher', 'section', 'studentCount',
            'recentSessions', 'todaySession', 'monthStats'
        ));
    }
}