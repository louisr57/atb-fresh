<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * The original `students_email_unique` DB-level index was created
     * before soft deletes were introduced. Since MySQL/MariaDB treat NULL
     * values as distinct from one another, a composite unique index on
     * (email, deleted_at) would NOT actually prevent duplicate *active*
     * students sharing an email (every row with deleted_at = NULL would be
     * considered unique). So instead we drop the DB-level constraint and
     * enforce "unique email among active (non-trashed) students" purely at
     * the application level (see StudentController validation and the
     * students:import command), which correctly understands soft deletes.
     */
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropUnique('students_email_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->unique('email', 'students_email_unique');
        });
    }
};
