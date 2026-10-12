<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Schedule::command('telescope:prune')->daily();

// Off-server, append-only copy of activity_log (no-op until ACTIVITY_JOURNAL_STORE is set in .env).
Schedule::command('activity:export-journal')->hourly()->withoutOverlapping(30);

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();
