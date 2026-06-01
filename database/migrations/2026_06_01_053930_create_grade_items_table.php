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
        Schema::create('grade_items', function (Blueprint $table) {
            $table->id();
             $table->foreignId('grade_scale_id')->constrained()->onDelete('cascade');
            $table->string('grade');            // A+, A, B, C, D, F
            $table->unsignedTinyInteger('min_marks');
            $table->unsignedTinyInteger('max_marks');
            $table->decimal('gpa', 3, 2)->default(0.00);
            $table->string('description')->nullable(); // Excellent, Very Good...
            $table->string('color')->nullable();       // hex color for UI badge
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grade_items');
    }
};
