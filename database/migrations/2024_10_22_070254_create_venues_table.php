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
        Schema::create('venues', function (Blueprint $table) {
            $table->id();
            $table->string('venue_name');
            $table->string('address');
            $table->string('city');
            $table->string('state');
            $table->string('country');
            $table->string('postcode');
            $table->string('location_geocode')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
        });

        // Modify events table to use venue_id
        Schema::table('events', function (Blueprint $table) {
            // Remove existing venue-related columns
            $table->dropColumn(['venue', 'city', 'state', 'country', 'postcode', 'location_geocode']);

            // Add venue_id foreign key
            $table->unsignedBigInteger('venue_id')->after('timeto');
            $table->foreign('venue_id')->references('id')->on('venues')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert events table changes
        Schema::table('events', function (Blueprint $table) {
            $table->dropForeign(['venue_id']);
            $table->dropColumn('venue_id');

            // Restore original columns
            $table->string('venue');
            $table->string('city');
            $table->string('state');
            $table->string('country');
            $table->string('postcode');
            $table->string('location_geocode')->nullable();
        });

        Schema::dropIfExists('venues');
    }
};
