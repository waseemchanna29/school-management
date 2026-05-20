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
       // Classes
        Schema::table('classes', function (Blueprint $table) {
            $table->foreignId('campus_id')->nullable()->constrained('campuses')->nullOnDelete()->after('id');
        });

        // Sections
        Schema::table('sections', function (Blueprint $table) {
            $table->foreignId('campus_id')->nullable()->constrained('campuses')->nullOnDelete()->after('id');
        });

        // Subjects
        Schema::table('subjects', function (Blueprint $table) {
            $table->foreignId('campus_id')->nullable()->constrained('campuses')->nullOnDelete()->after('id');
        });

        // Teachers
        Schema::table('teachers', function (Blueprint $table) {
            $table->foreignId('campus_id')->nullable()->constrained('campuses')->nullOnDelete()->after('user_id');
        });

        // Students
        Schema::table('students', function (Blueprint $table) {
            $table->foreignId('campus_id')->nullable()->constrained('campuses')->nullOnDelete()->after('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('classes',  fn($t) => $t->dropForeign(['campus_id']));
        Schema::table('sections', fn($t) => $t->dropForeign(['campus_id']));
        Schema::table('subjects', fn($t) => $t->dropForeign(['campus_id']));
        Schema::table('teachers', fn($t) => $t->dropForeign(['campus_id']));
        Schema::table('students', fn($t) => $t->dropForeign(['campus_id']));

        foreach (['classes','sections','subjects','teachers','students'] as $table) {
            Schema::table($table, fn($t) => $t->dropColumn('campus_id'));
        }
    }
};
