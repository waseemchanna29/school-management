<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\CampusContext;
use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Teacher;
use Illuminate\Http\Request;

class SectionController extends Controller
{
    public function index()
    {
        $campusId = CampusContext::id();
        $sections = Section::where('campus_id', $campusId)
            ->with(['schoolClass', 'students', 'classTeacher'])
            ->withCount('students')
            ->latest()->get();

        $classes  = SchoolClass::where('campus_id', $campusId)
            ->where('is_active', true)->orderBy('name')->get();

        $teachers = Teacher::where('campus_id', $campusId)
            ->where('is_active', true)->orderBy('full_name')->get();

        return view('admin.sections.index', compact('sections', 'classes', 'teachers'));
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

    public function assignClassTeacher(Request $request, Section $section)
    {
        if ($section->campus_id !== CampusContext::id()) abort(403);

        $request->validate([
            'class_teacher_id' => ['nullable', 'exists:teachers,id'],
        ]);

        // Remove this teacher from any other section they are class teacher of
        if ($request->class_teacher_id) {
            Section::where('campus_id', CampusContext::id())
                ->where('class_teacher_id', $request->class_teacher_id)
                ->where('id', '!=', $section->id)
                ->update(['class_teacher_id' => null]);
        }

        $section->update(['class_teacher_id' => $request->class_teacher_id]);

        $msg = $request->class_teacher_id
            ? 'Class teacher assigned successfully.'
            : 'Class teacher removed from section.';

        return back()->with('success', $msg);
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