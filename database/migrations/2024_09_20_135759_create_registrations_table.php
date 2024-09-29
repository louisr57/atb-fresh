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
        Schema::create('registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor('student_id')->constrained('students')->cascadeOnDelete(); // Ensure cascade delete if the student is deleted
            $table->foreignIdFor('event_id')->constrained('events')->cascadeOnDelete(); // Ensure cascade delete for events
            $table->string('end_status');
            $table->text('comments')->nullable(); // Make comments optional
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registrations');
    }
};
