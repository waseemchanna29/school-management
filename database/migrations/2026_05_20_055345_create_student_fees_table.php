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
        Schema::create('student_fees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->foreignId('campus_id')->constrained()->onDelete('cascade');
            $table->foreignId('fee_label_id')->constrained()->onDelete('cascade');
            $table->foreignId('fee_structure_item_id')->nullable()->constrained()->nullOnDelete(); // source template
            $table->string('academic_year');
            $table->decimal('amount', 10, 2);         // personal amount (may differ from structure)
            $table->boolean('is_active')->default(true);
            $table->text('note')->nullable();          // reason for custom amount
            
            $table->timestamps();
                   $table->unique(['student_id', 'fee_label_id', 'academic_year'], 'unique_student_fee');
     
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_fees');
    }
};
