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
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('roll_number')->unique();
            $table->string('gr_number')->unique();       // General Register number (Pakistan standard)
            $table->string('full_name');
            $table->string('father_name');
            $table->string('mother_name');
            $table->string('cnic')->nullable();          // B-Form / CNIC
            $table->string('phone')->nullable();
            $table->enum('gender', ['Male', 'Female', 'Other']);
            $table->date('date_of_birth');
            $table->string('religion')->nullable();
            $table->string('nationality')->default('Pakistani');
            $table->string('blood_group')->nullable();
            $table->text('address');
            $table->string('city');
            $table->string('district');
            $table->string('province');
            $table->foreignId('class_id')->constrained('classes')->onDelete('restrict');
            $table->foreignId('section_id')->constrained('sections')->onDelete('restrict');
            $table->date('admission_date');
            $table->string('previous_school')->nullable();
            $table->enum('status', ['active', 'inactive', 'transferred', 'expelled'])->default('active');
            $table->string('photo')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
