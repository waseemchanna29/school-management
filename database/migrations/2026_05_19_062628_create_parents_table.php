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
        Schema::create('parents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            // Father Info
            $table->string('father_full_name');
            $table->string('father_cnic')->nullable();
            $table->string('father_phone');
            $table->string('father_occupation')->nullable();
            $table->string('father_qualification')->nullable();
            $table->decimal('father_income', 12, 2)->nullable();
            $table->boolean('father_is_alive')->default(true);
            // Mother Info
            $table->string('mother_full_name');
            $table->string('mother_cnic')->nullable();
            $table->string('mother_phone')->nullable();
            $table->string('mother_occupation')->nullable();
            $table->string('mother_qualification')->nullable();
            $table->boolean('mother_is_alive')->default(true);
            // Guardian (if different)
            $table->string('guardian_name')->nullable();
            $table->string('guardian_relation')->nullable();
            $table->string('guardian_phone')->nullable();
            $table->string('guardian_cnic')->nullable();
            $table->text('guardian_address')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parents');
    }
};
