<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TruncateTable extends Command
{
    // The name and signature of the console command, accepting a table name as an argument.
    protected $signature = 'truncate:table {table}';

    // The console command description.
    protected $description = 'Truncate the specified table';

    // Execute the console command.
    public function handle()
    {
        // Get the table name passed as an argument
        $table = $this->argument('table');

        // Check if the table exists before truncating
        if (!\Schema::hasTable($table)) {
            $this->error("Table '{$table}' does not exist.");
            return;
        }

        // Truncate the specified table
        DB::table($table)->truncate();

        // Output success message
        $this->info("Table '{$table}' truncated successfully.");
    }
}
