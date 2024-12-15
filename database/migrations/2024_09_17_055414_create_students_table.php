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
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('gender')->nullable();
            $table->string('email')->unique();
            $table->string('phone_number')->unique();
            $table->string('address');
            $table->string('city');
            $table->string('state');
            $table->string('country');
            $table->string('post_code');
            $table->string('website')->default('N/A')->nullable();
            $table->date('dob')->nullable();
            $table->string('ident')->nullable();
            $table->string('next_of_kin')->nullable();
            $table->string('allergies')->default('N/A')->nullable();
            $table->text('special_needs')->nullable();
            $table->bigInteger('reg_count')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
