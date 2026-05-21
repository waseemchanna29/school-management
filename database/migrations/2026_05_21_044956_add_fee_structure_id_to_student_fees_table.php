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
        Schema::table('student_fees', function (Blueprint $table) {
            $table->foreignId('fee_structure_id')
                  ->nullable()
                  ->constrained('fee_structures')
                  ->nullOnDelete()
                  ->after('fee_label_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_fees', function (Blueprint $table) {
             $table->dropForeign(['fee_structure_id']);
            $table->dropColumn('fee_structure_id');
        });
    }
};
