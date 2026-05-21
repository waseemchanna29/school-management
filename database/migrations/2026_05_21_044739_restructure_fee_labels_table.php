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
         Schema::table('fee_labels', function (Blueprint $table) {
            $table->dropColumn('frequency');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fee_labels', function (Blueprint $table) {
            $table->enum('frequency', ['one_time', 'monthly', 'yearly'])->default('monthly')->after('name');
        });
    }
};
