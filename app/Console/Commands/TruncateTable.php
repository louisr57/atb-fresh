<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;  // Import Schema facade

class TruncateTable extends Command
{
    // The name and signature of the console command, accepting a table name as an argument.
    protected $signature = 'truncate:table {table}';

    // The console command description.
    protected $description = 'Truncate the specified table';

    // Execute the console command.
    public function handle()
    {
        // Safety: this is a destructive, unlogged, non-undoable operation
        // (it bypasses Eloquent events, so it is NOT recorded in the
        // activity_log and NOT affected by SoftDeletes). Never allow it to
        // run against production/staging data.
        if (! app()->environment('local', 'testing')) {
            $this->error(
                "Refusing to run: 'truncate:table' is disabled outside the local/testing environments ".
                "(current environment: '".app()->environment()."'). ".
                'This command bypasses Eloquent events (no activity log entry, no soft delete) '.
                'and cannot be undone. If you really need to clear data in production, use a '.
                'reviewed, logged, soft-delete-aware Eloquent operation instead.'
            );

            return 1;
        }

        // Get the table name passed as an argument
        $table = $this->argument('table');

        // Check if the table exists before truncating
        if (! Schema::hasTable($table)) {
            $this->error("Table '{$table}' does not exist.");

            return 1;
        }

        // Require explicit confirmation even in local/testing, since this
        // is still an irreversible operation.
        if (! $this->confirm("This will permanently truncate the '{$table}' table. Are you sure?")) {
            $this->info('Operation cancelled.');

            return 0;
        }

        // Truncate the specified table
        DB::table($table)->truncate();

        // Output success message
        $this->info("Table '{$table}' truncated successfully.");

        return 0;
    }
}
