<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\AttendanceSession;
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

        $section = $teacher->classTeacherOf()->with(['schoolClass', 'students'])->first();

        // Recent sessions
        $recentSessions = AttendanceSession::where('teacher_id', $teacher->id)
            ->with(['section', 'schoolClass'])
            ->latest('date')
            ->take(7)
            ->get();

        // Today's session
        $todaySession = AttendanceSession::where('teacher_id', $teacher->id)
            ->where('section_id', $section?->id)
            ->whereDate('date', today())
            ->first();

        // This month summary
        $monthStats = [
            'sessions'  => AttendanceSession::where('teacher_id', $teacher->id)
                ->whereMonth('date', date('n'))
                ->whereYear('date', date('Y'))
                ->count(),
            'submitted' => AttendanceSession::where('teacher_id', $teacher->id)
                ->whereMonth('date', date('n'))
                ->whereYear('date', date('Y'))
                ->where('status', 'submitted')
                ->count(),
        ];

        return view('teacher.dashboard', compact(
            'teacher', 'section', 'recentSessions', 'todaySession', 'monthStats'
        ));
    }
}