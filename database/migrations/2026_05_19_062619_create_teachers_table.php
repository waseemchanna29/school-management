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
        Schema::create('teachers', function (Blueprint $table) {
            $table->id();
             $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('employee_code')->unique();
            $table->string('full_name');
            $table->string('father_name');
            $table->string('cnic')->unique();
            $table->string('phone');
            $table->string('emergency_phone')->nullable();
            $table->enum('gender', ['Male', 'Female', 'Other']);
            $table->date('date_of_birth');
            $table->string('religion')->nullable();
            $table->string('nationality')->default('Pakistani');
            $table->string('domicile')->nullable();     // Province of domicile
            $table->text('address');
            $table->string('city');
            $table->string('district');
            $table->string('province');
            $table->string('qualification');            // Highest qualification
            $table->string('specialization')->nullable();
            $table->date('joining_date');
            $table->enum('employment_type', ['Permanent', 'Contract', 'Visiting', 'Part-time'])->default('Permanent');
            $table->decimal('salary', 10, 2)->nullable();
            $table->string('bank_name')->nullable();
            $table->string('bank_account')->nullable();
            $table->string('photo')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teachers');
    }
};
