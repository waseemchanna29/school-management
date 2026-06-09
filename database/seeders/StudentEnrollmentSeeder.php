<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Student;
use App\Models\StudentEnrollment;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StudentEnrollmentSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Creating enrollment records for existing students...');

        $created  = 0;
        $skipped  = 0;
        $noYear   = 0;

        // Get all campuses that have students
        $campusIds = Student::where('status', 'active')
            ->distinct()
            ->pluck('campus_id');

        foreach ($campusIds as $campusId) {
            // Find the current academic year for this campus
            $currentYear = AcademicYear::where('campus_id', $campusId)
                ->where('is_current', true)
                ->first();

            // If no current year, try latest year
            if (!$currentYear) {
                $currentYear = AcademicYear::where('campus_id', $campusId)
                    ->orderByDesc('start_date')
                    ->first();
            }

            if (!$currentYear) {
                $this->command->warn(
                    "No academic year found for campus_id={$campusId}. " .
                    "Please create one first."
                );
                $noYear++;
                continue;
            }

            // Get all active students for this campus who had class_id
            // NOTE: We read from the raw DB since class_id column may
            // still exist during this migration window
            $students = DB::table('students')
                ->where('campus_id', $campusId)
                ->where('status', 'active')
                ->get();

            foreach ($students as $student) {
                // Skip if already enrolled this year
                $exists = StudentEnrollment::where('student_id', $student->id)
                    ->where('academic_year_id', $currentYear->id)
                    ->exists();

                if ($exists) {
                    $skipped++;
                    continue;
                }

                // Use class_id/section_id from raw student record
                // (still exists before the column-drop migration runs)
                $classId   = $student->class_id   ?? null;
                $sectionId = $student->section_id ?? null;
                $rollNo    = $student->roll_number ?? null;

                StudentEnrollment::create([
                    'student_id'       => $student->id,
                    'academic_year_id' => $currentYear->id,
                    'campus_id'        => $campusId,
                    'class_id'         => $classId,
                    'section_id'       => $sectionId,
                    'roll_number'      => $rollNo,
                    'status'           => 'active',
                    'enrolled_at'      => now()->toDateString(),
                    'notes'            => 'Auto-migrated from existing student record.',
                ]);

                $created++;
            }
        }

        $this->command->info("Done. Created: {$created} | Skipped: {$skipped} | No year: {$noYear}");
    }
}