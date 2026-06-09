<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\AcademicYearContext;
use App\Helpers\CampusContext;
use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Student;
use App\Models\StudentEnrollment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EnrollmentController extends Controller
{
    // ── Helpers ───────────────────────────────────────────────────────────────

    private function campusId(): int
    {
        return CampusContext::id();
    }

    private function yearId(): int
    {
        return AcademicYearContext::id();
    }

    private function currentYear(): \App\Models\AcademicYear
    {
        return AcademicYearContext::current();
    }

    // ── Index — enrolled students this year ───────────────────────────────────
    public function index(Request $request)
    {
        $campusId = $this->campusId();
        $yearId   = $this->yearId();

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
            )->orWhere('roll_number', 'like', "%{$search}%");
        }

        $enrollments = $query->orderBy('roll_number')
            ->paginate(25);

        $classes  = SchoolClass::where('campus_id', $campusId)
            ->where('is_active', true)
            ->orderBy('name')->get();

        $sections = Section::where('campus_id', $campusId)
            ->where('is_active', true)
            ->with('schoolClass')->get();

        // Summary counts
        $summary = StudentEnrollment::where('academic_year_id', $yearId)
            ->where('campus_id', $campusId)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('admin.enrollment.index', compact(
            'enrollments', 'classes', 'sections', 'summary'
        ));
    }

    // ── Individual Enroll — show form ─────────────────────────────────────────
    public function create()
    {
        $campusId = $this->campusId();
        $yearId   = $this->yearId();

        // Students NOT yet enrolled this year (across all campuses — for transfers)
        $unenrolledStudents = Student::where('status', 'active')
            ->whereDoesntHave('enrollments', fn($q) => $q
                ->where('academic_year_id', $yearId)
            )
            ->orderBy('full_name')
            ->get();

        $classes  = SchoolClass::where('campus_id', $campusId)
            ->where('is_active', true)->orderBy('name')->get();

        $sections = Section::where('campus_id', $campusId)
            ->where('is_active', true)
            ->with('schoolClass')->get();

        return view('admin.enrollment.create', compact(
            'unenrolledStudents', 'classes', 'sections'
        ));
    }

    // ── Individual Enroll — store ─────────────────────────────────────────────
    public function store(Request $request)
    {
        $request->validate([
            'student_id'  => ['required', 'exists:students,id'],
            'class_id'    => ['required', 'exists:classes,id'],
            'section_id'  => ['required', 'exists:sections,id'],
            'roll_number' => ['nullable', 'string', 'max:20'],
            'enrolled_at' => ['nullable', 'date'],
            'notes'       => ['nullable', 'string'],
        ]);

        $yearId   = $this->yearId();
        $campusId = $this->campusId();

        // Check duplicate
        $exists = StudentEnrollment::where('student_id', $request->student_id)
            ->where('academic_year_id', $yearId)
            ->exists();

        if ($exists) {
            return back()->with('error',
                'This student is already enrolled in the current academic year.');
        }

        StudentEnrollment::create([
            'student_id'       => $request->student_id,
            'academic_year_id' => $yearId,
            'campus_id'        => $campusId,
            'class_id'         => $request->class_id,
            'section_id'       => $request->section_id,
            'roll_number'      => $request->roll_number,
            'status'           => 'active',
            'enrolled_at'      => $request->enrolled_at ?? now()->toDateString(),
            'notes'            => $request->notes,
        ]);

        return redirect()->route('admin.enrollment.index')
            ->with('success', 'Student enrolled successfully.');
    }

    // ── New Student Admission + Immediate Enrollment ──────────────────────────
    public function admissionCreate()
    {
        $campusId = $this->campusId();

        $classes  = SchoolClass::where('campus_id', $campusId)
            ->where('is_active', true)->orderBy('name')->get();

        $sections = Section::where('campus_id', $campusId)
            ->where('is_active', true)
            ->with('schoolClass')->get();

        return view('admin.enrollment.admission', compact('classes', 'sections'));
    }

    public function admissionStore(Request $request)
    {
        $request->validate([
            // Student personal info
            'full_name'       => ['required', 'string', 'max:200'],
            'father_name'     => ['required', 'string', 'max:200'],
            'mother_name'     => ['nullable', 'string', 'max:200'],
            'cnic'            => ['nullable', 'string', 'max:20'],
            'gender'          => ['required', 'in:male,female'],
            'date_of_birth'   => ['nullable', 'date'],
            'blood_group'     => ['nullable', 'string', 'max:5'],
            'phone'           => ['nullable', 'string', 'max:20'],
            'address'         => ['nullable', 'string'],
            'admission_date'  => ['required', 'date'],
            'previous_school' => ['nullable', 'string', 'max:255'],
            // Enrollment
            'class_id'        => ['required', 'exists:classes,id'],
            'section_id'      => ['required', 'exists:sections,id'],
            'roll_number'     => ['nullable', 'string', 'max:20'],
            'enrolled_at'     => ['nullable', 'date'],
        ]);

        DB::transaction(function () use ($request) {
            $campusId = $this->campusId();
            $yearId   = $this->yearId();

            // 1. Create student master record
            $student = Student::create([
                'campus_id'       => $campusId,
                'full_name'       => $request->full_name,
                'father_name'     => $request->father_name,
                'mother_name'     => $request->mother_name,
                'cnic'            => $request->cnic,
                'gender'          => $request->gender,
                'date_of_birth'   => $request->date_of_birth,
                'blood_group'     => $request->blood_group,
                'phone'           => $request->phone,
                'address'         => $request->address,
                'admission_date'  => $request->admission_date,
                'previous_school' => $request->previous_school,
                'status'          => 'active',
            ]);

            // 2. Create enrollment for current year
            StudentEnrollment::create([
                'student_id'       => $student->id,
                'academic_year_id' => $yearId,
                'campus_id'        => $campusId,
                'class_id'         => $request->class_id,
                'section_id'       => $request->section_id,
                'roll_number'      => $request->roll_number,
                'status'           => 'active',
                'enrolled_at'      => $request->enrolled_at ?? now()->toDateString(),
            ]);
        });

        return redirect()->route('admin.enrollment.index')
            ->with('success', 'New student admitted and enrolled successfully.');
    }

    // ── Edit Enrollment (class/section/roll) ──────────────────────────────────
    public function edit(StudentEnrollment $enrollment)
    {
        $this->gate($enrollment);

        $campusId = $this->campusId();
        $classes  = SchoolClass::where('campus_id', $campusId)
            ->where('is_active', true)->orderBy('name')->get();
        $sections = Section::where('campus_id', $campusId)
            ->where('is_active', true)
            ->with('schoolClass')->get();

        $enrollment->load(['student', 'schoolClass', 'section']);

        return view('admin.enrollment.edit',
            compact('enrollment', 'classes', 'sections'));
    }

    public function update(Request $request, StudentEnrollment $enrollment)
    {
        $this->gate($enrollment);

        $request->validate([
            'class_id'    => ['required', 'exists:classes,id'],
            'section_id'  => ['required', 'exists:sections,id'],
            'roll_number' => ['nullable', 'string', 'max:20'],
            'status'      => ['required', 'in:active,passed,detained,left,transferred'],
            'notes'       => ['nullable', 'string'],
        ]);

        $enrollment->update([
            'class_id'   => $request->class_id,
            'section_id' => $request->section_id,
            'roll_number'=> $request->roll_number,
            'status'     => $request->status,
            'notes'      => $request->notes,
        ]);

        return redirect()->route('admin.enrollment.index')
            ->with('success', 'Enrollment updated.');
    }

    // ── Bulk Update Status (e.g. mark whole class as passed) ─────────────────
    public function bulkStatus(Request $request)
    {
        $request->validate([
            'enrollment_ids'   => ['required', 'array'],
            'enrollment_ids.*' => ['exists:student_enrollments,id'],
            'status'           => ['required',
                'in:active,passed,detained,left,transferred'],
        ]);

        StudentEnrollment::whereIn('id', $request->enrollment_ids)
            ->where('academic_year_id', $this->yearId())
            ->where('campus_id', $this->campusId())
            ->update(['status' => $request->status]);

        return back()->with('success',
            count($request->enrollment_ids) . ' enrollment(s) updated to '
            . ucfirst($request->status) . '.');
    }

    // ── Bulk Carry Forward from Previous Year ─────────────────────────────────
    public function carryForwardCreate()
    {
        $campusId = $this->campusId();
        $yearId   = $this->yearId();

        $currentYear = $this->currentYear();

        // Get the previous academic year
        $previousYear = AcademicYear::where('campus_id', $campusId)
            ->where('id', '<', $yearId)
            ->orderByDesc('start_date')
            ->first();

        if (!$previousYear) {
            return redirect()->route('admin.enrollment.index')
                ->with('error',
                    'No previous academic year found to carry forward from.');
        }

        // Students enrolled in previous year but NOT in current year
        $previousEnrollments = StudentEnrollment::where('academic_year_id',
                $previousYear->id)
            ->where('campus_id', $campusId)
            ->whereIn('status', ['active', 'passed'])
            ->whereDoesntHave('student', fn($q) => $q
                ->whereHas('enrollments', fn($q2) => $q2
                    ->where('academic_year_id', $yearId)
                )
            )
            ->with(['student', 'schoolClass', 'section'])
            ->orderBy('class_id')
            ->get();

        $classes  = SchoolClass::where('campus_id', $campusId)
            ->where('is_active', true)->orderBy('name')->get();

        $sections = Section::where('campus_id', $campusId)
            ->where('is_active', true)
            ->with('schoolClass')->get();

        return view('admin.enrollment.carry-forward', compact(
            'previousEnrollments', 'previousYear',
            'currentYear', 'classes', 'sections'
        ));
    }

    public function carryForwardStore(Request $request)
    {
        $request->validate([
            'enrollments'              => ['required', 'array', 'min:1'],
            'enrollments.*.student_id' => ['required', 'exists:students,id'],
            'enrollments.*.class_id'   => ['required', 'exists:classes,id'],
            'enrollments.*.section_id' => ['required', 'exists:sections,id'],
            'enrollments.*.roll_number'=> ['nullable', 'string', 'max:20'],
        ]);

        $yearId   = $this->yearId();
        $campusId = $this->campusId();
        $created  = 0;
        $skipped  = 0;

        DB::transaction(function () use (
            $request, $yearId, $campusId, &$created, &$skipped
        ) {
            foreach ($request->enrollments as $data) {
                // Skip if already enrolled
                $exists = StudentEnrollment::where('student_id', $data['student_id'])
                    ->where('academic_year_id', $yearId)
                    ->exists();

                if ($exists) { $skipped++; continue; }

                StudentEnrollment::create([
                    'student_id'       => $data['student_id'],
                    'academic_year_id' => $yearId,
                    'campus_id'        => $campusId,
                    'class_id'         => $data['class_id'],
                    'section_id'       => $data['section_id'],
                    'roll_number'      => $data['roll_number'] ?? null,
                    'status'           => 'active',
                    'enrolled_at'      => now()->toDateString(),
                    'notes'            => 'Carried forward from previous year.',
                ]);

                $created++;
            }
        });

        return redirect()->route('admin.enrollment.index')
            ->with('success',
                "{$created} student(s) enrolled. {$skipped} already enrolled (skipped).");
    }

    // ── Delete enrollment ─────────────────────────────────────────────────────
    public function destroy(StudentEnrollment $enrollment)
    {
        $this->gate($enrollment);
        $enrollment->delete();
        return back()->with('success', 'Enrollment removed.');
    }

    private function gate(StudentEnrollment $e): void
    {
        if ($e->campus_id !== $this->campusId()) abort(403);
        if ($e->academic_year_id !== $this->yearId()) abort(403);
    }
}