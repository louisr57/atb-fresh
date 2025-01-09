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
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->date('datefrom');
            $table->date('dateto')->nullable();
            $table->time('timefrom');
            $table->time('timeto');
            $table->unsignedBigInteger('venue_id');
            $table->unsignedBigInteger('course_id');
            $table->unsignedBigInteger('facilitator_id');
            $table->text('remarks')->nullable();
            $table->integer('participant_count')->default(0);
            $table->timestamps();

            // Add foreign key constraints
            $table->foreign('venue_id')->references('id')->on('venues')->onDelete('cascade');
            $table->foreign('course_id')->references('id')->on('courses')->onDelete('cascade');
            $table->foreign('facilitator_id')->references('id')->on('facilitators')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
