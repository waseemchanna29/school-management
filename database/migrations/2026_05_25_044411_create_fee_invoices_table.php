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
        Schema::create('fee_invoices', function (Blueprint $table) {
            $table->id();
             $table->string('invoice_number')->unique();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->foreignId('campus_id')->constrained()->onDelete('cascade');
            $table->foreignId('fee_scheduler_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedTinyInteger('billing_month');   // 1-12
            $table->unsignedSmallInteger('billing_year');
            $table->string('billing_period_label');         // e.g. "May 2026"
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('outstanding', 10, 2)->default(0);  // manually added
            $table->decimal('fine', 10, 2)->default(0);
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('net_amount', 10, 2)->default(0);
            $table->decimal('paid_amount', 10, 2)->default(0);
            $table->decimal('balance', 10, 2)->default(0);
            $table->enum('status', ['unpaid', 'partial', 'paid', 'waived'])->default('unpaid');
            $table->date('due_date');
            $table->text('remarks')->nullable();
            $table->timestamps();

            // One invoice per student per month per year
            $table->unique(['student_id', 'billing_month', 'billing_year'], 'unique_invoice');
       
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fee_invoices');
    }
};
