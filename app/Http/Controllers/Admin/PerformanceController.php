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
use App\Models\StudentMark;
use App\Models\Subject;
use App\Services\PerformanceService;
use Illuminate\Http\Request;

class PerformanceController extends Controller
{
    public function __construct(private PerformanceService $perf) {}

    private function yearId(): int
    {
        return AcademicYearContext::id();
    }

    // ── Index — all marks for current year ────────────────────────────────────
    public function index(Request $request)
    {
        $campusId = CampusContext::id();
        $yearId   = $this->yearId();

        $query = StudentMark::where('campus_id', $campusId)
            ->where('academic_year_id', $yearId)    // ← FK
            ->with(['student', 'subject', 'teacher']);

        if ($request->filled('class_id')) {
            $query->whereHas('student', fn($q) => $q
                ->whereHas('enrollments', fn($eq) => $eq
                    ->where('class_id', $request->class_id)
                    ->where('academic_year_id', $yearId)
                )
            );
        }
        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }
        if ($request->filled('term')) {
            $query->where('term', $request->term);
        }

        $marks    = $query->latest()->paginate(25);
        $classes  = SchoolClass::where('campus_id', $campusId)
            ->where('is_active', true)->get();
        $subjects = Subject::where('campus_id', $campusId)
            ->where('is_active', true)->get();
        $years    = $this->academicYears();
        $terms    = PerformanceService::TERMS;

        return view('admin.performance.index',
            compact('marks', 'classes', 'subjects', 'years', 'terms'));
    }

    // ── Student report ────────────────────────────────────────────────────────
    public function studentReport(Request $request, Student $student)
    {
        if ($student->campus_id !== CampusContext::id()) abort(403);

        $yearId = (int) $request->get('academic_year_id', $this->yearId());
        $term   = (int) $request->get('term', 1);

        $report = $this->perf->getStudentReport($student, $yearId, $term);

        $years = $this->academicYears();
        $terms = PerformanceService::TERMS;

        // Load enrollment for the selected year
        $enrollment = StudentEnrollment::where('student_id', $student->id)
            ->where('academic_year_id', $yearId)
            ->with(['schoolClass', 'section'])
            ->first();

        return view('admin.performance.student-report', compact(
            'student', 'report', 'years', 'terms',
            'yearId', 'term', 'enrollment'
        ));
    }

    // ── Class report ──────────────────────────────────────────────────────────
    public function classReport(Request $request)
    {
        $campusId = CampusContext::id();
        $yearId   = $this->yearId();

        $examType  = $request->get('exam_type', 'final');
        $classId   = $request->get('class_id');
        $subjectId = $request->get('subject_id');
        $term      = (int) $request->get('term', 1);

        $classes  = SchoolClass::where('campus_id', $campusId)
            ->where('is_active', true)->get();

        $subjects = $classId
            ? Subject::where('campus_id', $campusId)
                ->where('class_id', $classId)->get()
            : collect();

        $weights = $this->perf->getExamWeights($campusId);
        $marks   = collect();

        if ($classId && $subjectId) {
            $marks = $this->perf->getClassSubjectReport(
                $campusId, $classId, $subjectId,
                $yearId, $term, $examType    // ← yearId FK
            );
        }

        $years = $this->academicYears();
        $terms = PerformanceService::TERMS;

        return view('admin.performance.class-report', compact(
            'classes', 'subjects', 'weights', 'marks',
            'years', 'terms', 'yearId', 'term',
            'classId', 'subjectId', 'examType'
        ));
    }

    private function academicYears(): \Illuminate\Database\Eloquent\Collection
    {
        return AcademicYear::where('campus_id', CampusContext::id())
            ->orderByDesc('start_date')->get();
    }
}