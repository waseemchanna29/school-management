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
        Schema::create('timetable_entries', function (Blueprint $table) {
            $table->id();
           $table->foreignId('timetable_id')->constrained()->onDelete('cascade');
            $table->foreignId('period_template_id')->constrained()->onDelete('cascade');
            $table->enum('day', ['Mon','Tue','Wed','Thu','Fri','Sat']);
            $table->enum('type', ['lesson','break','free'])->default('free');
            $table->foreignId('subject_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('teacher_id')->nullable()->constrained()->nullOnDelete();
            $table->string('custom_label')->nullable();   // for breaks: "Lunch", "Prayer"
            $table->timestamps();

            $table->unique(['timetable_id','period_template_id','day'], 'unique_slot');
  
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('timetable_entries');
    }
};
