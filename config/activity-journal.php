<?php

/*
|--------------------------------------------------------------------------
| Activity journal (append-only, off-server copy of activity_log)
|--------------------------------------------------------------------------
|
| Used by `php artisan activity:export-journal`. Leave ACTIVITY_JOURNAL_STORE
| unset to disable the export entirely.
|
*/

return [

    /*
     * Where journal files are written: 'rclone' (any rclone remote, e.g. Backblaze B2),
     * 'local' (a directory, used for tests and development) or null (disabled).
     */
    'store' => env('ACTIVITY_JOURNAL_STORE'),

    /* rclone destination such as "b2journal:atb-fresh-activity-journal". */
    'rclone_remote' => env('ACTIVITY_JOURNAL_RCLONE_REMOTE'),

    'rclone_binary' => env('ACTIVITY_JOURNAL_RCLONE_BINARY', 'rclone'),

    'local_path' => env('ACTIVITY_JOURNAL_LOCAL_PATH', storage_path('app/activity-journal')),

    /* Maximum rows per exported file. */
    'batch_size' => (int) env('ACTIVITY_JOURNAL_BATCH_SIZE', 5000),

    /*
     * Rows younger than this many seconds are left for the next run, so a row
     * committed slightly out of id order is not skipped.
     */
    'settle_seconds' => (int) env('ACTIVITY_JOURNAL_SETTLE_SECONDS', 60),

    /*
     * Optional Healthchecks.io style URL. Pinged after a good run; "/fail" is
     * appended when the export fails or an integrity ALERT is raised.
     */
    'ping_url' => env('ACTIVITY_JOURNAL_PING_URL'),
];
