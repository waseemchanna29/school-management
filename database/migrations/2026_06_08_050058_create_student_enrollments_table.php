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
        Schema::create('student_enrollments', function (Blueprint $table) {
            $table->id();
             $table->foreignId('student_id')
                  ->constrained()
                  ->onDelete('cascade');
            $table->foreignId('academic_year_id')
                  ->constrained()
                  ->onDelete('cascade');
            $table->foreignId('campus_id')
                  ->constrained()
                  ->onDelete('cascade');
            $table->foreignId('class_id')
                  ->constrained('classes')
                  ->onDelete('cascade');
            $table->foreignId('section_id')
                  ->constrained('sections')
                  ->onDelete('cascade');
            $table->string('roll_number')->nullable();
            $table->enum('status', [
                'active',
                'passed',
                'detained',
                'left',
                'transferred',
            ])->default('active');
            $table->date('enrolled_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            // One enrollment per student per academic year
            $table->unique(
                ['student_id', 'academic_year_id'],
                'unique_student_year'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_enrollments');
    }
};
