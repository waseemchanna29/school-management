<?php

namespace Database\Seeders;

use App\Models\ExamTypeWeight;
use App\Models\GradeItem;
use App\Models\GradeScale;
use Illuminate\Database\Seeder;

class GradeSystemSeeder extends Seeder
{
    public function run(): void
    {
        // ── Global Grade Scale ──────────────────────────────────────────────
        $scale = GradeScale::firstOrCreate(
            ['campus_id' => null, 'is_default' => true],
            ['name' => 'Standard Pakistani Grading Scale', 'is_active' => true]
        );

        $grades = [
            ['grade' => 'A+', 'min' => 90, 'max' => 100, 'gpa' => 4.0,  'desc' => 'Excellent',      'color' => '#198754', 'sort' => 1],
            ['grade' => 'A',  'min' => 80, 'max' => 89,  'gpa' => 3.7,  'desc' => 'Very Good',       'color' => '#0dcaf0', 'sort' => 2],
            ['grade' => 'B+', 'min' => 75, 'max' => 79,  'gpa' => 3.3,  'desc' => 'Good',            'color' => '#2563a8', 'sort' => 3],
            ['grade' => 'B',  'min' => 65, 'max' => 74,  'gpa' => 3.0,  'desc' => 'Above Average',   'color' => '#6f42c1', 'sort' => 4],
            ['grade' => 'C',  'min' => 55, 'max' => 64,  'gpa' => 2.0,  'desc' => 'Average',         'color' => '#e8a020', 'sort' => 5],
            ['grade' => 'D',  'min' => 45, 'max' => 54,  'gpa' => 1.0,  'desc' => 'Below Average',   'color' => '#fd7e14', 'sort' => 6],
            ['grade' => 'F',  'min' => 0,  'max' => 44,  'gpa' => 0.0,  'desc' => 'Fail',            'color' => '#dc3545', 'sort' => 7],
        ];

        foreach ($grades as $g) {
            GradeItem::updateOrCreate(
                ['grade_scale_id' => $scale->id, 'grade' => $g['grade']],
                [
                    'min_marks'   => $g['min'],
                    'max_marks'   => $g['max'],
                    'gpa'         => $g['gpa'],
                    'description' => $g['desc'],
                    'color'       => $g['color'],
                    'sort_order'  => $g['sort'],
                ]
            );
        }

        // ── Global Exam Type Weights ────────────────────────────────────────
        $weights = [
            ['type' => 'class_test',  'label' => 'Class Test',      'weight' => 10, 'sort' => 1],
            ['type' => 'quiz',        'label' => 'Quiz',             'weight' => 10, 'sort' => 2],
            ['type' => 'assignment',  'label' => 'Assignment',       'weight' => 10, 'sort' => 3],
            ['type' => 'mid_term',    'label' => 'Mid Term Exam',    'weight' => 30, 'sort' => 4],
            ['type' => 'final',       'label' => 'Final Exam',       'weight' => 40, 'sort' => 5],
        ];

        foreach ($weights as $w) {
            ExamTypeWeight::updateOrCreate(
                ['campus_id' => null, 'exam_type' => $w['type']],
                [
                    'label'      => $w['label'],
                    'weight'     => $w['weight'],
                    'is_active'  => true,
                    'sort_order' => $w['sort'],
                ]
            );
        }
    }
}