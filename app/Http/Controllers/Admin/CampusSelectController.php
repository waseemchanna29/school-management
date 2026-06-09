<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\AcademicYearContext;
use App\Helpers\CampusContext;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CampusSelectController extends Controller
{
    public function show()
    {
        $user = Auth::user();

        if ($user->isSuperAdmin()) {
            return redirect()->route('super.dashboard');
        }

        $campuses = $user->campuses()->where('is_active', true)->get();

        if ($campuses->isEmpty()) {
            Auth::logout();
            return redirect()->route('login')
                ->with('error', 'You have no assigned campuses. Please contact the Super Admin.');
        }

        return view('admin.campus-select', compact('campuses'));
    }

    public function select(Request $request)
    {
        $request->validate([
            'campus_id' => ['required', 'exists:campuses,id'],
        ]);

        $user = Auth::user();

        if (!$user->isSuperAdmin() && !CampusContext::adminHasAccess($request->campus_id)) {
            return back()->with('error', 'You do not have access to this campus.');
        }

        CampusContext::set((int) $request->campus_id);
        AcademicYearContext::clear();
        return $this->redirectToYearSelection((int) $request->campus_id);
        // return redirect()->route('admin.dashboard')
        //     ->with('success', 'Campus selected successfully.');
    }

    private function redirectToYearSelection(int $campusId)
    {
        $years = \App\Models\AcademicYear::where('campus_id', $campusId)
            ->orderByDesc('start_date')
            ->get();

        if ($years->count() === 1) {
            AcademicYearContext::set($years->first()->id);
            return redirect()->route('admin.dashboard')
                ->with('success', 'Campus selected successfully.');
        }

        $currentYear = $years->firstWhere('is_current', true);
        if ($currentYear) {
            AcademicYearContext::set($currentYear->id);
            return redirect()->route('admin.dashboard')
                ->with('success', 'Campus selected successfully.');
        }

        return redirect()->route('academic-year.select');
    }


    public function switchCampus()
    {
        AcademicYearContext::clear();
        CampusContext::clear();
        return redirect()->route('campus.select');
    }
}
