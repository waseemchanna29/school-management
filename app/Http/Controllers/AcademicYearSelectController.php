<?php

namespace App\Http\Controllers;

use App\Helpers\AcademicYearContext;
use App\Helpers\CampusContext;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AcademicYearSelectController extends Controller
{
    public function show()
    {
        $user = Auth::user();

        // Super admin never needs to select a year
        if ($user->isSuperAdmin()) {
            return redirect()->route('super.dashboard');
        }

        // Determine campus ID based on role
        $campusId = $user->isTeacher()
            ? $user->teacher?->campus_id
            : CampusContext::id();

        if (!$campusId) {
            return redirect()->route('campus.select')
                ->with('error', 'Please select a campus first.');
        }

        $years = AcademicYear::where('campus_id', $campusId)
            ->orderByDesc('start_date')
            ->get();

        // If no years exist, redirect admin to create one
        if ($years->isEmpty()) {
            if ($user->isAdmin()) {
                return redirect()->route('admin.academic-years.index')
                    ->with('info', 'No academic years found. Please create one first.');
            }
            // Teacher with no years
            Auth::logout();
            return redirect()->route('login')
                ->with('error', 'No academic years available. Contact your administrator.');
        }

        // Auto-select if only one year exists
        if ($years->count() === 1) {
            AcademicYearContext::set($years->first()->id);
            return $this->redirectAfterSelect($user);
        }

        return view('academic-year.select', compact('years'));
    }

    public function select(Request $request)
    {
        $request->validate([
            'academic_year_id' => ['required', 'exists:academic_years,id'],
        ]);

        $user     = Auth::user();
        $campusId = $user->isTeacher()
            ? $user->teacher?->campus_id
            : CampusContext::id();

        // Verify this year belongs to the user's campus
        $year = AcademicYear::where('id', $request->academic_year_id)
            ->where('campus_id', $campusId)
            ->firstOrFail();

        AcademicYearContext::set($year->id);

        return $this->redirectAfterSelect($user);
    }

    public function switchYear()
    {
        AcademicYearContext::clear();
        return redirect()->route('academic-year.select');
    }

    private function redirectAfterSelect($user)
    {
        return $user->isTeacher()
            ? redirect()->route('teacher.dashboard')
            : redirect()->route('admin.dashboard');
    }
}