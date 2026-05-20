<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\CampusContext;
use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use App\Models\Section;
use Illuminate\Http\Request;

class SectionController extends Controller
{
    public function index()
    {
        $campusId = CampusContext::id();
        $sections = Section::where('campus_id', $campusId)
            ->with('schoolClass')->withCount('students')->latest()->get();
        $classes  = SchoolClass::where('campus_id', $campusId)
            ->where('is_active', true)->orderBy('name')->get();
        return view('admin.sections.index', compact('sections', 'classes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'class_id' => ['required', 'exists:classes,id'],
            'name'     => ['required', 'string', 'max:10'],
        ]);

        Section::create([
            'campus_id' => CampusContext::id(),
            'class_id'  => $request->class_id,
            'name'      => $request->name,
            'is_active' => true,
        ]);

        return back()->with('success', 'Section added.');
    }

    public function destroy(Section $section)
    {
        if ($section->campus_id !== CampusContext::id()) abort(403);
        if ($section->students()->count() > 0) {
            return back()->with('error', 'Cannot delete — section has students.');
        }
        $section->delete();
        return back()->with('success', 'Section deleted.');
    }
}