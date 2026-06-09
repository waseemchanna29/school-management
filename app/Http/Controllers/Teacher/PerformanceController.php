<?php

namespace App\Http\Controllers\Teacher;

use App\Helpers\AcademicYearContext;
use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\StudentMark;
use App\Models\Subject;
use App\Services\PerformanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PerformanceController extends Controller
{
    public function __construct(private PerformanceService $perf) {}

    private function teacher()
    {
        return Auth::user()->teacher;
    }

    private function yearId(): int
    {
        return AcademicYearContext::id();
    }

    // ── Subjects list ─────────────────────────────────────────────────────────
    public function subjects()
    {
        $teacher = $this->teacher();
        $subjects = $teacher->subjects()
            ->with('schoolClass')
            ->where('is_active', true)
            ->get();

        $terms   = PerformanceService::TERMS;
        $weights = $this->perf->getExamWeights($teacher->campus_id);

        return view('teacher.performance.subjects',
            compact('teacher', 'subjects', 'terms', 'weights'));
    }

    // ── Enter marks ───────────────────────────────────────────────────────────
    public function enterMarks(Request $request, Subject $subject)
    {
        $teacher = $this->teacher();
        $yearId  = $this->yearId();

        // Verify teacher is assigned this subject
        $assigned = $teacher->subjects()
            ->where('subject_id', $subject->id)->exists();
        if (!$assigned) abort(403, 'You are not assigned to this subject.');

        $term     = (int) $request->get('term', 1);
        $examType = $request->get('exam_type', 'class_test');

        $weights = $this->perf->getExamWeights($teacher->campus_id);
        $weight  = $weights->firstWhere('exam_type', $examType);

        // Students via enrollment in this subject's class
        $students = Student::whereHas('enrollments', fn($q) => $q
            ->where('class_id', $subject->class_id)
            ->where('campus_id', $teacher->campus_id)
            ->where('academic_year_id', $yearId)    // ← NEW
            ->where('status', 'active')
        )
        ->with(['enrollments' => fn($q) => $q
            ->where('academic_year_id', $yearId)
        ])
        ->orderBy('full_name')
        ->get()
        ->each(fn($s) => $s->enrollment = $s->enrollments->first());

        // Existing marks for this batch
        $existingMarks = StudentMark::where('subject_id', $subject->id)
            ->where('academic_year_id', $yearId)    // ← FK
            ->where('term', $term)
            ->where('exam_type', $examType)
            ->whereIn('student_id', $students->pluck('id'))
            ->get()
            ->keyBy('student_id');

        $years = $this->academicYears();
        $terms = PerformanceService::TERMS;

        return view('teacher.performance.enter-marks', compact(
            'teacher', 'subject', 'students', 'existingMarks',
            'weights', 'weight', 'yearId', 'term', 'examType',
            'years', 'terms'
        ));
    }

    // ── Save marks ────────────────────────────────────────────────────────────
    public function saveMarks(Request $request, Subject $subject)
    {
        $teacher = $this->teacher();
        $yearId  = $this->yearId();

        $assigned = $teacher->subjects()
            ->where('subject_id', $subject->id)->exists();
        if (!$assigned) abort(403);

        $request->validate([
            'term'             => ['required', 'integer', 'min:1', 'max:3'],
            'exam_type'        => ['required', 'string'],
            'exam_date'        => ['required', 'date'],
            'total_marks'      => ['required', 'numeric', 'min:1', 'max:1000'],
            'marks'            => ['required', 'array'],
            'marks.*.obtained' => ['nullable', 'numeric', 'min:0'],
            'marks.*.remarks'  => ['nullable', 'string', 'max:200'],
        ]);

        DB::transaction(function () use ($request, $subject, $teacher, $yearId) {
            foreach ($request->marks as $studentId => $data) {
                $obtained = $data['obtained'] ?? null;
                if (is_null($obtained) || $obtained === '') continue;

                StudentMark::updateOrCreate(
                    [
                        'student_id'       => $studentId,
                        'subject_id'       => $subject->id,
                        'academic_year_id' => $yearId,    // ← FK
                        'term'             => $request->term,
                        'exam_type'        => $request->exam_type,
                    ],
                    [
                        'teacher_id'     => $teacher->id,
                        'campus_id'      => $teacher->campus_id,
                        'marks_obtained' => $obtained,
                        'total_marks'    => $request->total_marks,
                        'remarks'        => $data['remarks'] ?? null,
                        'exam_date'      => $request->exam_date,
                    ]
                );
            }
        });

        return back()->with('success', 'Marks saved successfully.');
    }

    // ── Marks history ─────────────────────────────────────────────────────────
    public function history(Request $request)
    {
        $teacher = $this->teacher();
        $yearId  = $this->yearId();

        $term = $request->get('term', 1);

        $marks = StudentMark::where('teacher_id', $teacher->id)
            ->where('academic_year_id', $yearId)    // ← FK
            ->where('term', $term)
            ->with(['student.enrollments', 'subject'])
            ->latest()
            ->paginate(25);

        $years = $this->academicYears();
        $terms = PerformanceService::TERMS;

        return view('teacher.performance.history',
            compact('teacher', 'marks', 'years', 'terms', 'yearId', 'term'));
    }

    private function academicYears(): \Illuminate\Database\Eloquent\Collection
    {
        return \App\Models\AcademicYear::where(
            'campus_id',
            $this->teacher()->campus_id
        )
        ->orderByDesc('start_date')
        ->get();
    }
}