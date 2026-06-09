<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\AcademicYearContext;
use App\Helpers\CampusContext;
use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Student;
use App\Models\StudentEnrollment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class StudentController extends Controller
{
    // ── Index — year-aware student list ──────────────────────────────────────
    public function index(Request $request)
    {
        $campusId = CampusContext::id();
        $yearId   = AcademicYearContext::id();

        // Base query: students enrolled this year in this campus
        $query = StudentEnrollment::where('academic_year_id', $yearId)
            ->where('campus_id', $campusId)
            ->with(['student', 'schoolClass', 'section']);

        // Filters
        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }
        if ($request->filled('section_id')) {
            $query->where('section_id', $request->section_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('student', fn($q) => $q
                ->where('full_name', 'like', "%{$search}%")
                ->orWhere('cnic', 'like', "%{$search}%")
                ->orWhere('father_name', 'like', "%{$search}%")
            )->orWhere('roll_number', 'like', "%{$search}%");
        }

        $enrollments = $query->orderBy('roll_number')->paginate(25);

        $classes  = SchoolClass::where('campus_id', $campusId)
            ->where('is_active', true)->orderBy('name')->get();
        $sections = Section::where('campus_id', $campusId)
            ->where('is_active', true)
            ->with('schoolClass')->get();

        return view('admin.students.index',
            compact('enrollments', 'classes', 'sections'));
    }

    // ── Show student profile ──────────────────────────────────────────────────
    public function show(Student $student)
    {
        $this->gate($student);

        $yearId = AcademicYearContext::id();

        // Current year enrollment
        $enrollment = StudentEnrollment::where('student_id', $student->id)
            ->where('academic_year_id', $yearId)
            ->with(['schoolClass', 'section', 'academicYear'])
            ->first();

        // All enrollment history
        $allEnrollments = StudentEnrollment::where('student_id', $student->id)
            ->with(['schoolClass', 'section', 'academicYear', 'campus'])
            ->orderByDesc('academic_year_id')
            ->get();

        $student->load(['parentRecord', 'campus']);

        return view('admin.students.show',
            compact('student', 'enrollment', 'allEnrollments'));
    }

    // ── Edit student personal info ────────────────────────────────────────────
    public function edit(Student $student)
    {
        $this->gate($student);
        return view('admin.students.edit', compact('student'));
    }

    // ── Update student personal info ──────────────────────────────────────────
    public function update(Request $request, Student $student)
    {
        $this->gate($student);

        $request->validate([
            'full_name'     => ['required', 'string', 'max:200'],
            'father_name'   => ['required', 'string', 'max:200'],
            'mother_name'   => ['nullable', 'string', 'max:200'],
            'cnic'          => ['nullable', 'string', 'max:20'],
            'gender'        => ['required', 'in:male,female'],
            'date_of_birth' => ['nullable', 'date'],
            'blood_group'   => ['nullable', 'string', 'max:5'],
            'phone'         => ['nullable', 'string', 'max:20'],
            'address'       => ['nullable', 'string'],
            'photo'         => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:1024'],
            'status'        => ['required', 'in:active,left,graduated'],
        ]);

        if ($request->hasFile('photo')) {
            if ($student->photo) {
                Storage::disk('public')->delete($student->photo);
            }
            $student->photo = $request->file('photo')
                ->store('students', 'public');
        }

        $student->update([
            'full_name'     => $request->full_name,
            'father_name'   => $request->father_name,
            'mother_name'   => $request->mother_name,
            'cnic'          => $request->cnic,
            'gender'        => $request->gender,
            'date_of_birth' => $request->date_of_birth,
            'blood_group'   => $request->blood_group,
            'phone'         => $request->phone,
            'address'       => $request->address,
            'status'        => $request->status,
            'photo'         => $student->photo,
        ]);

        return redirect()->route('admin.students.show', $student)
            ->with('success', 'Student profile updated.');
    }

    // ── Destroy ───────────────────────────────────────────────────────────────
    public function destroy(Student $student)
    {
        $this->gate($student);

        if ($student->enrollments()->count() > 0) {
            return back()->with('error',
                'Cannot delete student with enrollment records. ' .
                'Remove all enrollments first.');
        }

        if ($student->photo) {
            Storage::disk('public')->delete($student->photo);
        }

        $student->delete();

        return redirect()->route('admin.students.index')
            ->with('success', 'Student deleted.');
    }

    private function gate(Student $student): void
    {
        // Student belongs to any campus the admin manages
        $adminCampusIds = auth()->user()
            ->campuses->pluck('id')->toArray();

        if (!in_array($student->campus_id, $adminCampusIds)) {
            abort(403);
        }
    }
}