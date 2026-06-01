<?php

namespace App\Services;

use App\Models\Campus;
use App\Models\ExamTypeWeight;
use App\Models\GradeItem;
use App\Models\GradeScale;
use App\Models\Student;
use App\Models\StudentMark;
use App\Models\Subject;
use Illuminate\Support\Collection;

class PerformanceService
{
    /**
     * Get the active grade scale for a campus.
     * Falls back to global default if campus has no custom scale.
     */
    public function getGradeScale(int $campusId): ?GradeScale
    {
        // Campus-specific active scale
        $scale = GradeScale::where('campus_id', $campusId)
            ->where('is_active', true)
            ->with('items')
            ->first();

        if ($scale) return $scale;

        // Fall back to global default
        return GradeScale::whereNull('campus_id')
            ->where('is_default', true)
            ->where('is_active', true)
            ->with('items')
            ->first();
    }

    /**
     * Get exam type weights for a campus.
     * Falls back to global if campus has none.
     */
    public function getExamWeights(int $campusId): Collection
    {
        $weights = ExamTypeWeight::where('campus_id', $campusId)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        if ($weights->isNotEmpty()) return $weights;

        return ExamTypeWeight::whereNull('campus_id')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * Calculate weighted average for a student's subject in a term.
     * Returns percentage (0-100).
     */
    public function calculateWeightedAverage(
        Collection $marks,
        Collection $weights
    ): float {
        $totalWeight    = 0;
        $weightedTotal  = 0;

        foreach ($weights as $weight) {
            $mark = $marks->firstWhere('exam_type', $weight->exam_type);
            if (!$mark) continue;

            $percentage    = $mark->percentage;
            $w             = (float) $weight->weight;
            $weightedTotal += $percentage * ($w / 100);
            $totalWeight   += $w;
        }

        if ($totalWeight <= 0) return 0;

        // Normalize to 100 in case not all exam types are filled
        return round(($weightedTotal / $totalWeight) * 100, 2);
    }

    /**
     * Get full performance report for a student.
     * Returns per-subject, per-term breakdown with grades.
     */
    public function getStudentReport(
        Student $student,
        string  $academicYear,
        int     $term
    ): array {
        $campusId = $student->campus_id;
        $scale    = $this->getGradeScale($campusId);
        $weights  = $this->getExamWeights($campusId);

        // All marks for this student this year+term
        $allMarks = StudentMark::where('student_id', $student->id)
            ->where('academic_year', $academicYear)
            ->where('term', $term)
            ->with('subject')
            ->get();

        // Group by subject
        $bySubject = $allMarks->groupBy('subject_id');

        $subjectResults = [];
        $totalWeightedAvg = 0;
        $subjectCount   = 0;

        foreach ($bySubject as $subjectId => $marks) {
            $subject = $marks->first()->subject;
            $weightedAvg = $this->calculateWeightedAverage($marks, $weights);
            $grade       = $scale?->getGrade($weightedAvg);

            $examBreakdown = [];
            foreach ($weights as $weight) {
                $mark = $marks->firstWhere('exam_type', $weight->exam_type);
                $examBreakdown[] = [
                    'exam_type'  => $weight->exam_type,
                    'label'      => $weight->label,
                    'weight'     => $weight->weight,
                    'mark'       => $mark,
                    'percentage' => $mark ? $mark->percentage : null,
                ];
            }

            $subjectResults[] = [
                'subject'         => $subject,
                'marks'           => $marks,
                'exam_breakdown'  => $examBreakdown,
                'weighted_avg'    => $weightedAvg,
                'grade'           => $grade,
            ];

            $totalWeightedAvg += $weightedAvg;
            $subjectCount++;
        }

        $overallAvg   = $subjectCount > 0 ? round($totalWeightedAvg / $subjectCount, 2) : 0;
        $overallGrade = $scale?->getGrade($overallAvg);

        return [
            'student'         => $student,
            'academic_year'   => $academicYear,
            'term'            => $term,
            'subject_results' => $subjectResults,
            'overall_avg'     => $overallAvg,
            'overall_grade'   => $overallGrade,
            'scale'           => $scale,
            'weights'         => $weights,
        ];
    }

    /**
     * Get class-level performance summary for a subject.
     */
    public function getClassSubjectReport(
        int    $campusId,
        int    $classId,
        int    $subjectId,
        string $academicYear,
        int    $term,
        string $examType
    ): Collection {
        $weights = $this->getExamWeights($campusId);
        $scale   = $this->getGradeScale($campusId);

        $marks = StudentMark::where('campus_id', $campusId)
            ->where('subject_id', $subjectId)
            ->where('academic_year', $academicYear)
            ->where('term', $term)
            ->where('exam_type', $examType)
            ->with(['student.section'])
            ->whereHas('student', fn($q) => $q->where('class_id', $classId))
            ->get()
            ->map(function ($mark) use ($scale) {
                $mark->grade = $scale?->getGrade($mark->percentage);
                return $mark;
            });

        return $marks->sortBy('student.full_name')->values();
    }

    /**
     * Copy global default grade scale to a campus.
     */
    public function copyGlobalToCampus(int $campusId, string $name): GradeScale
    {
        $global = GradeScale::whereNull('campus_id')
            ->where('is_default', true)
            ->with('items')
            ->firstOrFail();

        $newScale = GradeScale::create([
            'campus_id'  => $campusId,
            'name'       => $name,
            'is_default' => false,
            'is_active'  => true,
        ]);

        foreach ($global->items as $item) {
            $newScale->items()->create([
                'grade'       => $item->grade,
                'min_marks'   => $item->min_marks,
                'max_marks'   => $item->max_marks,
                'gpa'         => $item->gpa,
                'description' => $item->description,
                'color'       => $item->color,
                'sort_order'  => $item->sort_order,
            ]);
        }

        return $newScale;
    }

    /**
     * Copy global exam weights to a campus.
     */
    public function copyGlobalWeightsToCampus(int $campusId): void
    {
        $globals = ExamTypeWeight::whereNull('campus_id')
            ->where('is_active', true)
            ->get();

        foreach ($globals as $g) {
            ExamTypeWeight::firstOrCreate(
                ['campus_id' => $campusId, 'exam_type' => $g->exam_type],
                [
                    'label'      => $g->label,
                    'weight'     => $g->weight,
                    'is_active'  => true,
                    'sort_order' => $g->sort_order,
                ]
            );
        }
    }

    public const TERMS = [
        1 => 'Term 1 (First)',
        2 => 'Term 2 (Mid)',
        3 => 'Term 3 (Final)',
    ];
}