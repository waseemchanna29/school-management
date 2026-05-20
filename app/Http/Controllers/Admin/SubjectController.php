<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\CampusContext;
use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use App\Models\Subject;
use Illuminate\Http\Request;

class SubjectController extends Controller
{
    public function index()
    {
        $campusId = CampusContext::id();
        $subjects = Subject::where('campus_id', $campusId)->with('schoolClass')->latest()->get();
        $classes  = SchoolClass::where('campus_id', $campusId)
            ->where('is_active', true)->orderBy('name')->get();
        return view('admin.subjects.index', compact('subjects', 'classes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'     => ['required', 'string', 'max:150'],
            'code'     => ['required', 'string', 'max:20', 'unique:subjects,code'],
            'class_id' => ['required', 'exists:classes,id'],
        ]);

        Subject::create(array_merge(
            $request->only('name', 'code', 'class_id'),
            ['campus_id' => CampusContext::id(), 'is_active' => true]
        ));

        return back()->with('success', "Subject \"{$request->name}\" added.");
    }

    public function destroy(Subject $subject)
    {
        if ($subject->campus_id !== CampusContext::id()) abort(403);
        $subject->delete();
        return back()->with('success', 'Subject deleted.');
    }
}