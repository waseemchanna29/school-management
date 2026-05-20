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
        Schema::create('student_education_records', function (Blueprint $table) {
            $table->id();
             $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->string('level');              // e.g. Primary, Middle, Matric, Intermediate
            $table->string('institution_name');
            $table->string('board_university')->nullable();
            $table->year('passing_year');
            $table->string('total_marks')->nullable();
            $table->string('obtained_marks')->nullable();
            $table->string('grade_division')->nullable();   // e.g. A+, First Division
            $table->string('certificate_number')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_education_records');
    }
};
