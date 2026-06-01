<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\ExamTypeWeight;
use App\Models\Student;
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

    private function currentAcademicYear(): string
    {
        $y = (int) date('Y');
        return date('n') >= 4 ? "$y-" . ($y + 1) : ($y - 1) . "-$y";
    }

    // Teacher: list their assigned subjects to enter marks
    public function subjects()
    {
        $teacher  = $this->teacher();
        $subjects = $teacher->subjects()
            ->with('schoolClass')
            ->where('is_active', true)
            ->get();

        $terms    = PerformanceService::TERMS;
        $weights  = $this->perf->getExamWeights($teacher->campus_id);

        return view('teacher.performance.subjects', compact(
            'teacher', 'subjects', 'terms', 'weights'
        ));
    }

    // Teacher: enter marks for a subject
    public function enterMarks(Request $request, Subject $subject)
    {
        $teacher = $this->teacher();

        // Verify teacher is assigned this subject
        $assigned = $teacher->subjects()->where('subject_id', $subject->id)->exists();
        if (!$assigned) abort(403, 'You are not assigned to this subject.');

        $academicYear = $request->get('academic_year', $this->currentAcademicYear());
        $term         = (int) $request->get('term', 1);
        $examType     = $request->get('exam_type', 'class_test');

        $weights = $this->perf->getExamWeights($teacher->campus_id);
        $weight  = $weights->firstWhere('exam_type', $examType);

        // Students in this subject's class
        $students = Student::where('class_id', $subject->class_id)
            ->where('campus_id', $teacher->campus_id)
            ->where('status', 'active')
            ->orderBy('full_name')
            ->get();

        // Existing marks for this batch
        $existingMarks = StudentMark::where('subject_id', $subject->id)
            ->where('academic_year', $academicYear)
            ->where('term', $term)
            ->where('exam_type', $examType)
            ->whereIn('student_id', $students->pluck('id'))
            ->get()
            ->keyBy('student_id');

        $years = $this->academicYears();
        $terms = PerformanceService::TERMS;

        return view('teacher.performance.enter-marks', compact(
            'teacher', 'subject', 'students', 'existingMarks',
            'weights', 'weight', 'academicYear', 'term', 'examType',
            'years', 'terms'
        ));
    }

    // Teacher: save marks
    public function saveMarks(Request $request, Subject $subject)
    {
        $teacher = $this->teacher();

        $assigned = $teacher->subjects()->where('subject_id', $subject->id)->exists();
        if (!$assigned) abort(403);

        $request->validate([
            'academic_year'    => ['required', 'string'],
            'term'             => ['required', 'integer', 'min:1', 'max:3'],
            'exam_type'        => ['required', 'string'],
            'exam_date'        => ['required', 'date'],
            'total_marks'      => ['required', 'numeric', 'min:1', 'max:1000'],
            'marks'            => ['required', 'array'],
            'marks.*.obtained' => ['nullable', 'numeric', 'min:0'],
            'marks.*.remarks'  => ['nullable', 'string', 'max:200'],
        ]);

        DB::transaction(function () use ($request, $subject, $teacher) {
            foreach ($request->marks as $studentId => $data) {
                $obtained = $data['obtained'] ?? null;

                if (is_null($obtained) || $obtained === '') continue;

                StudentMark::updateOrCreate(
                    [
                        'student_id'    => $studentId,
                        'subject_id'    => $subject->id,
                        'academic_year' => $request->academic_year,
                        'term'          => $request->term,
                        'exam_type'     => $request->exam_type,
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

    // Teacher: view marks they entered
    public function history(Request $request)
    {
        $teacher      = $this->teacher();
        $academicYear = $request->get('academic_year', $this->currentAcademicYear());
        $term         = $request->get('term', 1);

        $marks = StudentMark::where('teacher_id', $teacher->id)
            ->where('academic_year', $academicYear)
            ->where('term', $term)
            ->with(['student.schoolClass', 'subject'])
            ->latest()
            ->paginate(25);

        $years = $this->academicYears();
        $terms = PerformanceService::TERMS;

        return view('teacher.performance.history', compact(
            'teacher', 'marks', 'years', 'terms', 'academicYear', 'term'
        ));
    }

    private function academicYears(): array
    {
        $years = [];
        $start = (int) date('Y') - 1;
        for ($i = $start; $i <= $start + 3; $i++) {
            $years[] = $i . '-' . ($i + 1);
        }
        return $years;
    }
}