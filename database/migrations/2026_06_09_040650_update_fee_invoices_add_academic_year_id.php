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
        // ── Step 1: Add academic_year_id ──────────────────────────────────────
        // Schema::table('fee_invoices', function (Blueprint $table) {
        //     $table->foreignId('academic_year_id')
        //         ->nullable()
        //         ->after('fee_scheduler_id')
        //         ->constrained('academic_years')
        //         ->nullOnDelete();
        // });

      
        // ── Step 4: Drop billing_year (keep billing_month — still needed) ─────
        // Schema::table('fee_invoices', function (Blueprint $table) {
        //     $table->dropColumn('billing_year');
        // });

        // ── Step 5: Drop old unique constraint and re-create ──────────────────
        Schema::table('fee_invoices', function (Blueprint $table) {
            try {
                $table->dropUnique('unique_invoice');
            } catch (\Exception $e) {
            }

            // New unique: one invoice per student per month per academic year
            $table->unique(
                ['student_id', 'billing_month', 'academic_year_id'],
                'unique_invoice'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fee_invoices', function (Blueprint $table) {
            try {
                $table->dropUnique('unique_invoice');
            } catch (\Exception $e) {
            }
            $table->unsignedSmallInteger('billing_year')->nullable()->after('billing_month');
        });

        DB::statement('
            UPDATE fee_invoices fi
            JOIN academic_years y ON y.id = fi.academic_year_id
            SET fi.billing_year = SUBSTRING_INDEX(y.name, "-", 1)
        ');

        Schema::table('fee_invoices', function (Blueprint $table) {
            $table->dropForeign(['academic_year_id']);
            $table->dropColumn('academic_year_id');
            $table->unique(
                ['student_id', 'billing_month', 'billing_year'],
                'unique_invoice'
            );
        });
    }
};
