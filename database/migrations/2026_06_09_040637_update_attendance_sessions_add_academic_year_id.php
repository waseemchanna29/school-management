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
       // ── Step 1: Add nullable FK column ───────────────────────────────────
        Schema::table('attendance_sessions', function (Blueprint $table) {
            $table->foreignId('academic_year_id')
                  ->nullable()
                  ->after('academic_year')
                  ->constrained('academic_years')
                  ->nullOnDelete();
        });

        // ── Step 2: Populate FK by matching name string ───────────────────────
        DB::statement('
            UPDATE attendance_sessions s
            JOIN academic_years y
                ON y.campus_id = s.campus_id
               AND y.name      = s.academic_year
            SET s.academic_year_id = y.id
        ');

        // ── Step 3: Log any unmatched rows (won't block migration) ────────────
        $unmatched = DB::table('attendance_sessions')
            ->whereNull('academic_year_id')
            ->count();

        if ($unmatched > 0) {
            \Illuminate\Support\Facades\Log::warning(
                "Phase 2C: {$unmatched} attendance_sessions rows could not be " .
                "matched to an academic year. They have academic_year_id = NULL."
            );
        }

        // ── Step 4: Drop old string column ────────────────────────────────────
        Schema::table('attendance_sessions', function (Blueprint $table) {
            $table->dropColumn('academic_year');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendance_sessions', function (Blueprint $table) {
            $table->string('academic_year')->nullable()->after('date');
        });

        DB::statement('
            UPDATE attendance_sessions s
            JOIN academic_years y ON y.id = s.academic_year_id
            SET s.academic_year = y.name
        ');

        Schema::table('attendance_sessions', function (Blueprint $table) {
            $table->dropForeign(['academic_year_id']);
            $table->dropColumn('academic_year_id');
        });
    }
};
