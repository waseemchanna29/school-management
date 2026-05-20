<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\CampusContext;
use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;

class DashboardController extends Controller
{
    public function index()
    {
        $campusId = CampusContext::id();

        $stats = [
            'students'        => Student::where('campus_id', $campusId)->count(),
            'active_students' => Student::where('campus_id', $campusId)->where('status', 'active')->count(),
            'teachers'        => Teacher::where('campus_id', $campusId)->count(),
            'active_teachers' => Teacher::where('campus_id', $campusId)->where('is_active', true)->count(),
            'classes'         => SchoolClass::where('campus_id', $campusId)->where('is_active', true)->count(),
            'subjects'        => Subject::where('campus_id', $campusId)->where('is_active', true)->count(),
        ];

        $recentStudents = Student::where('campus_id', $campusId)
            ->with(['schoolClass', 'section'])->latest()->take(6)->get();

        $recentTeachers = Teacher::where('campus_id', $campusId)
            ->with('user')->latest()->take(5)->get();

        return view('admin.dashboard', compact('stats', 'recentStudents', 'recentTeachers'));
    }
}