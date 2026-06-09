<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // ── Step 1: Add nullable FK ───────────────────────────────────────────
        Schema::table('student_marks', function (Blueprint $table) {
            $table->foreignId('academic_year_id')
                ->nullable()
                ->after('academic_year')
                ->constrained('academic_years')
                ->nullOnDelete();
        });

        // ── Step 2: Populate by matching name + campus ────────────────────────
       
       
        // ── Step 4: Drop old column ────────────────────────────────────────────
        Schema::table('student_marks', function (Blueprint $table) {
            $table->dropColumn('academic_year');
        });

        // ── Step 5: Update unique constraint ──────────────────────────────────
        // Old: student_id, subject_id, exam_type, term, academic_year
        // New: student_id, subject_id, exam_type, term, academic_year_id
        Schema::table('student_marks', function (Blueprint $table) {
            // try {
            //     $table->dropUnique('unique_student_mark');
            // } catch (\Exception $e) {
            //     // May not exist if named differently
            // }

            $table->unique(
                ['student_id', 'subject_id', 'exam_type', 'term', 'academic_year_id'],
                'unique_student_mark'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_marks', function (Blueprint $table) {
            try {
                $table->dropUnique('unique_student_mark');
            } catch (\Exception $e) {
            }
            $table->string('academic_year')->nullable()->after('term');
        });

        Schema::table('student_marks', function (Blueprint $table) {
            $table->dropForeign(['academic_year_id']);
            $table->dropColumn('academic_year_id');
            $table->unique(
                ['student_id', 'subject_id', 'exam_type', 'term', 'academic_year'],
                'unique_student_mark'
            );
        });
    }
};
