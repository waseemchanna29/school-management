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
        Schema::create('fee_labels', function (Blueprint $table) {
            $table->id();
             $table->foreignId('campus_id')->constrained()->onDelete('cascade');
            $table->string('name');                  // e.g. Tuition Fee, Exam Fee
            $table->enum('frequency', ['one_time', 'monthly', 'yearly']);
            $table->boolean('is_active')->default(true);
           
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fee_labels');
    }
};
