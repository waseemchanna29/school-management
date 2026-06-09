<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\AcademicYearContext;
use App\Helpers\CampusContext;
use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use Illuminate\Http\Request;

class AcademicYearController extends Controller
{
    public function index()
    {
        $years = AcademicYear::where('campus_id', CampusContext::id())
            ->orderByDesc('start_date')
            ->get();

        return view('admin.academic-years.index', compact('years'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'       => ['required', 'string', 'max:20'],
            'start_date' => ['required', 'date'],
            'end_date'   => ['required', 'date', 'after:start_date'],
            'notes'      => ['nullable', 'string'],
        ]);

        $campusId = CampusContext::id();

        $exists = AcademicYear::where('campus_id', $campusId)
            ->where('name', $request->name)
            ->exists();

        if ($exists) {
            return back()->with('error', "Academic year \"{$request->name}\" already exists.");
        }

        AcademicYear::create([
            'campus_id'  => $campusId,
            'name'       => $request->name,
            'start_date' => $request->start_date,
            'end_date'   => $request->end_date,
            'is_current' => false,
            'is_locked'  => false,
            'notes'      => $request->notes,
        ]);

        return back()->with('success', "Academic year \"{$request->name}\" created.");
    }

    public function update(Request $request, AcademicYear $academicYear)
    {
        $this->gate($academicYear);

        if ($academicYear->is_locked) {
            return back()->with('error', 'Cannot edit a locked academic year.');
        }

        $request->validate([
            'name'       => ['required', 'string', 'max:20'],
            'start_date' => ['required', 'date'],
            'end_date'   => ['required', 'date', 'after:start_date'],
            'notes'      => ['nullable', 'string'],
        ]);

        $academicYear->update([
            'name'       => $request->name,
            'start_date' => $request->start_date,
            'end_date'   => $request->end_date,
            'notes'      => $request->notes,
        ]);

        return back()->with('success', 'Academic year updated.');
    }

    public function setCurrent(AcademicYear $academicYear)
    {
        $this->gate($academicYear);

        // Unset current from all years in this campus
        AcademicYear::where('campus_id', $academicYear->campus_id)
            ->update(['is_current' => false]);

        $academicYear->update(['is_current' => true]);

        // If admin is currently using a different year, update session
        if (AcademicYearContext::id() !== $academicYear->id) {
            AcademicYearContext::set($academicYear->id);
        }

        return back()->with('success', "\"{$academicYear->name}\" set as current academic year.");
    }

    public function toggleLock(AcademicYear $academicYear)
    {
        $this->gate($academicYear);

        $academicYear->update(['is_locked' => !$academicYear->is_locked]);

        $status = $academicYear->is_locked ? 'locked' : 'unlocked';
        return back()->with('success', "Academic year \"{$academicYear->name}\" {$status}.");
    }

    public function destroy(AcademicYear $academicYear)
    {
        $this->gate($academicYear);

        if ($academicYear->is_current) {
            return back()->with('error', 'Cannot delete the current academic year.');
        }

        $academicYear->delete();
        return back()->with('success', 'Academic year deleted.');
    }

    private function gate(AcademicYear $year): void
    {
        if ($year->campus_id !== CampusContext::id()) abort(403);
    }
}