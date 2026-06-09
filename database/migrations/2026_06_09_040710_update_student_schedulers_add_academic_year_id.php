<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('student_schedulers', function (Blueprint $table) {
            $table->foreignId('academic_year_id')
                  ->nullable()
                  ->after('campus_id')
                  ->constrained('academic_years')
                  ->nullOnDelete();
        });

     
        // Update unique constraint
        Schema::table('student_schedulers', function (Blueprint $table) {
            // Old: student_id unique
            // New: student_id + academic_year_id unique
            // (student can have different scheduler per year)
            try {
                $table->dropUnique(['student_id']);
            } catch (\Exception $e) {}

            $table->unique(
                ['student_id', 'academic_year_id'],
                'unique_student_scheduler_year'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_schedulers', function (Blueprint $table) {
            try {
                $table->dropUnique('unique_student_scheduler_year');
            } catch (\Exception $e) {}

            $table->dropForeign(['academic_year_id']);
            $table->dropColumn('academic_year_id');

            try {
                $table->unique(['student_id']);
            } catch (\Exception $e) {}
        });
    }
};
