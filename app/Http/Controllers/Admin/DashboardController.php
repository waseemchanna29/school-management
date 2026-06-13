<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\AcademicYearContext;
use App\Helpers\CampusContext;
use App\Http\Controllers\Controller;
use App\Models\AttendanceSession;
use App\Models\FeeInvoice;
use App\Models\Section;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\Teacher;

class DashboardController extends Controller
{
    public function index()
    {
        $campusId = CampusContext::id();
        $yearId   = AcademicYearContext::id();

        // ── Year-aware stats ──────────────────────────────────────────────────
        $totalStudents = StudentEnrollment::where('campus_id', $campusId)
            ->where('academic_year_id', $yearId)
            ->where('status', 'active')
            ->count();

        $totalTeachers = Teacher::where('campus_id', $campusId)
            ->where('is_active', true)
            ->count();

        $totalSections = Section::where('campus_id', $campusId)
            ->where('is_active', true)
            ->count();

        // Attendance today
        $todayAttendance = AttendanceSession::where('campus_id', $campusId)
            ->where('academic_year_id', $yearId)
            ->whereDate('date', today())
            ->where('status', 'submitted')
            ->count();

        // Fee stats for current year
        $unpaidInvoices = FeeInvoice::where('campus_id', $campusId)
            ->where('academic_year_id', $yearId)
            ->where('status', 'unpaid')
            ->count();

        $totalCollection = FeeInvoice::where('campus_id', $campusId)
            ->where('academic_year_id', $yearId)
            ->sum('paid_amount');

        // Enrollment breakdown
        $enrollmentBreakdown = StudentEnrollment::where('campus_id', $campusId)
            ->where('academic_year_id', $yearId)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $activeYear = AcademicYearContext::current();

        $recentStudents = StudentEnrollment::where('campus_id', $campusId)
            ->where('academic_year_id', $yearId)
            ->with(['schoolClass', 'section', 'student'])->latest()->take(6)->get();


        $recentTeachers = Teacher::where('campus_id', $campusId)
            ->with('user')->latest()->take(5)->get();
        return view('admin.dashboard', compact(
            'totalStudents',
            'totalTeachers',
            'totalSections',
            'todayAttendance',
            'unpaidInvoices',
            'totalCollection',
            'enrollmentBreakdown',
            'activeYear',
            'recentStudents',
            'recentTeachers'
        ));
    }
}
