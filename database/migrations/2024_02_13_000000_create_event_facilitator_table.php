<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('event_facilitator', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->onDelete('cascade');
            $table->foreignId('facilitator_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            // Prevent duplicate assignments
            $table->unique(['event_id', 'facilitator_id']);
        });

        // Copy existing facilitator assignments to the new pivot table
        DB::table('events')
            ->whereNotNull('facilitator_id')
            ->select('id', 'facilitator_id')
            ->orderBy('id')
            ->chunk(100, function ($events) {
                foreach ($events as $event) {
                    DB::table('event_facilitator')->insert([
                        'event_id' => $event->id,
                        'facilitator_id' => $event->facilitator_id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            });

        // Remove the old facilitator_id column
        Schema::table('events', function (Blueprint $table) {
            $table->dropForeign(['facilitator_id']);
            $table->dropColumn('facilitator_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Add back the facilitator_id column
        Schema::table('events', function (Blueprint $table) {
            $table->foreignId('facilitator_id')->nullable()->constrained();
        });

        // Copy the first facilitator assignment back to the events table
        DB::table('event_facilitator')
            ->select('event_id', 'facilitator_id')
            ->orderBy('created_at')
            ->get()
            ->groupBy('event_id')
            ->each(function ($assignments, $eventId) {
                // Take the first facilitator for each event
                $firstAssignment = $assignments->first();
                DB::table('events')
                    ->where('id', $eventId)
                    ->update(['facilitator_id' => $firstAssignment->facilitator_id]);
            });

        Schema::dropIfExists('event_facilitator');
    }
};
