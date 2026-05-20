<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\CampusContext;
use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use Illuminate\Http\Request;

class ClassController extends Controller
{
    public function index()
    {
        $campusId = CampusContext::id();
        $classes  = SchoolClass::where('campus_id', $campusId)
            ->withCount(['sections', 'students', 'subjects'])->latest()->get();
        return view('admin.classes.index', compact('classes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'        => ['required', 'string', 'max:100'],
            'grade_level' => ['required', 'string'],
        ]);

        SchoolClass::create([
            'campus_id'   => CampusContext::id(),
            'name'        => $request->name,
            'grade_level' => $request->grade_level,
            'is_active'   => true,
        ]);

        return back()->with('success', "Class \"{$request->name}\" added.");
    }

    public function update(Request $request, SchoolClass $class)
    {
        $this->authorizeCampus($class->campus_id);
        $request->validate([
            'name'        => ['required', 'string', 'max:100'],
            'grade_level' => ['required', 'string'],
        ]);
        $class->update(['name' => $request->name, 'grade_level' => $request->grade_level]);
        return back()->with('success', 'Class updated.');
    }

    public function destroy(SchoolClass $class)
    {
        $this->authorizeCampus($class->campus_id);
        if ($class->students()->count() > 0) {
            return back()->with('error', 'Cannot delete — class has enrolled students.');
        }
        $class->delete();
        return back()->with('success', 'Class deleted.');
    }

    private function authorizeCampus(?int $campusId): void
    {
        if ($campusId !== CampusContext::id()) abort(403);
    }
}