<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Virtual venues (e.g. Zoom) have no room limit, so they are exempt from the
     * "one event per venue at a time" rule. Facilitator clashes still apply.
     */
    public function up(): void
    {
        Schema::table('venues', function (Blueprint $table) {
            $table->boolean('is_virtual')->default(false)->after('venue_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('venues', function (Blueprint $table) {
            $table->dropColumn('is_virtual');
        });
    }
};
