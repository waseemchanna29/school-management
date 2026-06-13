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
        Schema::create('teacher_academic_years', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')
                ->constrained()
                ->onDelete('cascade');
            $table->foreignId('academic_year_id')
                ->constrained()
                ->onDelete('cascade');
            $table->timestamps();

            // A teacher can only be assigned a year once
            $table->unique(
                ['teacher_id', 'academic_year_id'],
                'unique_teacher_year'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teacher_academic_years');
    }
};
