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

        // Super admin never needs year selection
        if ($user->isSuperAdmin()) {
            return redirect()->route('super.dashboard');
        }

        if ($user->isTeacher()) {
            return $this->showForTeacher($user);
        }

        return $this->showForAdmin($user);
    }

    // ── Teacher year selection ────────────────────────────────────────────────
    private function showForTeacher($user)
    {
        $teacher = $user->teacher;

        if (!$teacher) {
            Auth::logout();
            return redirect()->route('login')
                ->with('error', 'Teacher profile not found.');
        }

        // Only years admin has assigned to this teacher
        $years = $teacher->academicYears()
            ->orderByDesc('start_date')
            ->get();

        // No years assigned — show error, do not proceed
        if ($years->isEmpty()) {
            return view('academic-year.select', [
                'years'         => collect(),
                'noYearsError'  => true,
                'errorMessage'  => 'No academic years have been assigned to you yet. ' .
                                   'Please contact your administrator.',
            ]);
        }

        // Auto-select if only one year assigned
        if ($years->count() === 1) {
            AcademicYearContext::set($years->first()->id);
            return redirect()->route('teacher.dashboard');
        }

        // Auto-select current year if assigned
        $currentYear = $years->firstWhere('is_current', true);
        if ($currentYear) {
            AcademicYearContext::set($currentYear->id);
            return redirect()->route('teacher.dashboard');
        }

        return view('academic-year.select', [
            'years'        => $years,
            'noYearsError' => false,
            'errorMessage' => null,
        ]);
    }

    // ── Admin year selection ──────────────────────────────────────────────────
    private function showForAdmin($user)
    {
        $campusId = CampusContext::id();

        if (!$campusId) {
            return redirect()->route('campus.select')
                ->with('error', 'Please select a campus first.');
        }

        $years = AcademicYear::where('campus_id', $campusId)
            ->orderByDesc('start_date')
            ->get();

        if ($years->isEmpty()) {
            return redirect()->route('admin.academic-years.index')
                ->with('info',
                    'No academic years found. Please create one first.');
        }

        // Auto-select if only one
        if ($years->count() === 1) {
            AcademicYearContext::set($years->first()->id);
            return redirect()->route('admin.dashboard');
        }

        // Auto-select current year
        $currentYear = $years->firstWhere('is_current', true);
        if ($currentYear) {
            AcademicYearContext::set($currentYear->id);
            return redirect()->route('admin.dashboard');
        }

        return view('academic-year.select', [
            'years'        => $years,
            'noYearsError' => false,
            'errorMessage' => null,
        ]);
    }

    // ── Process selection ─────────────────────────────────────────────────────
    public function select(Request $request)
    {
        $request->validate([
            'academic_year_id' => ['required', 'exists:academic_years,id'],
        ]);

        $user   = Auth::user();
        $yearId = (int) $request->academic_year_id;

        if ($user->isTeacher()) {
            // Verify teacher is authorized for this year
            $teacher = $user->teacher;
            if (!$teacher || !$teacher->hasYearAccess($yearId)) {
                return back()->with('error',
                    'You are not authorized to access that academic year.');
            }

            AcademicYearContext::set($yearId);
            return redirect()->route('teacher.dashboard');
        }

        // Admin — verify year belongs to campus
        $campusId = CampusContext::id();
        $year     = AcademicYear::where('id', $yearId)
            ->where('campus_id', $campusId)
            ->firstOrFail();

        AcademicYearContext::set($year->id);
        return redirect()->route('admin.dashboard');
    }

    // ── Switch year ───────────────────────────────────────────────────────────
    public function switchYear()
    {
        AcademicYearContext::clear();
        return redirect()->route('academic-year.select');
    }
}