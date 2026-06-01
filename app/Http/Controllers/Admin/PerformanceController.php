<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\CampusContext;
use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Student;
use App\Models\StudentMark;
use App\Models\Subject;
use App\Services\PerformanceService;
use Illuminate\Http\Request;

class PerformanceController extends Controller
{
    public function __construct(private PerformanceService $perf) {}

    private function academicYears(): array
    {
        $years = [];
        $start = (int) date('Y') - 1;
        for ($i = $start; $i <= $start + 3; $i++) {
            $years[] = $i . '-' . ($i + 1);
        }
        return $years;
    }

    private function currentAcademicYear(): string
    {
        $y = (int) date('Y');
        return date('n') >= 4 ? "$y-" . ($y + 1) : ($y - 1) . "-$y";
    }

    // Admin: view all marks
    public function index(Request $request)
    {
        $campusId = CampusContext::id();
        $query    = StudentMark::where('campus_id', $campusId)
            ->with(['student.schoolClass', 'subject', 'teacher']);

        if ($request->filled('class_id'))   $query->whereHas('student', fn($q) => $q->where('class_id', $request->class_id));
        if ($request->filled('subject_id')) $query->where('subject_id', $request->subject_id);
        if ($request->filled('term'))       $query->where('term', $request->term);
        if ($request->filled('academic_year')) $query->where('academic_year', $request->academic_year);

        $marks    = $query->latest()->paginate(25);
        $classes  = SchoolClass::where('campus_id', $campusId)->where('is_active', true)->get();
        $subjects = Subject::where('campus_id', $campusId)->where('is_active', true)->get();
        $years    = $this->academicYears();
        $terms    = PerformanceService::TERMS;

        return view('admin.performance.index', compact('marks', 'classes', 'subjects', 'years', 'terms'));
    }

    // Admin: full student performance report
    public function studentReport(Request $request, Student $student)
    {
        if ($student->campus_id !== CampusContext::id()) abort(403);

        $academicYear = $request->get('academic_year', $this->currentAcademicYear());
        $term         = (int) $request->get('term', 1);

        $report = $this->perf->getStudentReport($student, $academicYear, $term);
        $years  = $this->academicYears();
        $terms  = PerformanceService::TERMS;

        $student->load(['schoolClass', 'section']);

        return view('admin.performance.student-report', compact(
            'student', 'report', 'years', 'terms', 'academicYear', 'term'
        ));
    }

    // Admin: class performance overview
    public function classReport(Request $request)
    {
        $campusId     = CampusContext::id();
        $academicYear = $request->get('academic_year', $this->currentAcademicYear());
        $term         = (int) $request->get('term', 1);
        $classId      = $request->get('class_id');
        $subjectId    = $request->get('subject_id');
        $examType     = $request->get('exam_type', 'final');

        $classes  = SchoolClass::where('campus_id', $campusId)->where('is_active', true)->get();
        $subjects = $classId
            ? Subject::where('campus_id', $campusId)->where('class_id', $classId)->get()
            : collect();

        $weights = $this->perf->getExamWeights($campusId);
        $marks   = collect();

        if ($classId && $subjectId) {
            $marks = $this->perf->getClassSubjectReport(
                $campusId, $classId, $subjectId, $academicYear, $term, $examType
            );
        }

        $years = $this->academicYears();
        $terms = PerformanceService::TERMS;

        return view('admin.performance.class-report', compact(
            'classes', 'subjects', 'weights', 'marks',
            'years', 'terms', 'academicYear', 'term',
            'classId', 'subjectId', 'examType'
        ));
    }
}