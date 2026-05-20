<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Campus;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'campuses'        => Campus::count(),
            'active_campuses' => Campus::where('is_active', true)->count(),
            'admins'          => User::where('role', 'admin')->count(),
            'teachers'        => Teacher::count(),
            'students'        => Student::count(),
        ];

        $campuses = Campus::withCount(['teachers', 'students', 'admins'])
            ->latest()->get();

        return view('super.dashboard', compact('stats', 'campuses'));
    }
}