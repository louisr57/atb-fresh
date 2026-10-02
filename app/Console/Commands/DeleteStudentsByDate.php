<?php

namespace App\Console\Commands;

use App\Models\Student;
use Illuminate\Console\Command;
use Carbon\Carbon;

class DeleteStudentsByDate extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'students:delete-by-date {date : The date to delete students from (YYYY-MM-DD format)} {--confirm : Skip confirmation prompt}';

    /**
     * The console command description.
     */
    protected $description = 'Delete all students created on a specific date (cascade deletes will apply to related records)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dateInput = $this->argument('date');
        $skipConfirmation = $this->option('confirm');

        // Validate date format
        try {
            $date = Carbon::createFromFormat('Y-m-d', $dateInput);
        } catch (\Exception $e) {
            $this->error('Invalid date format. Please use YYYY-MM-DD format (e.g., 2024-01-15)');
            return 1;
        }

        // Find students created on the specified date
        $students = Student::whereDate('created_at', $date->format('Y-m-d'))->get();

        if ($students->isEmpty()) {
            $this->info("No students found created on {$date->format('Y-m-d')}");
            return 0;
        }

        // Display students to be deleted
        $this->info("Found {$students->count()} student(s) created on {$date->format('Y-m-d')}:");
        $this->table(
            ['ID', 'Name', 'Email', 'Created At'],
            $students->map(function ($student) {
                return [
                    $student->id,
                    $student->first_name . ' ' . $student->last_name,
                    $student->email,
                    $student->created_at->format('Y-m-d H:i:s')
                ];
            })
        );

        // Count related registrations that will be cascade deleted
        $totalRegistrations = 0;
        foreach ($students as $student) {
            $totalRegistrations += $student->registrations()->count();
        }

        if ($totalRegistrations > 0) {
            $this->warn("This will also cascade delete {$totalRegistrations} related registration(s)");
        }

        // Confirmation prompt (unless --confirm flag is used)
        if (!$skipConfirmation) {
            if (!$this->confirm('Are you sure you want to delete these students and their related records?')) {
                $this->info('Operation cancelled.');
                return 0;
            }
        }

        // Delete students (cascade deletes will handle related records)
        $deletedCount = 0;
        $this->info('Deleting students...');

        foreach ($students as $student) {
            try {
                $student->delete();
                $deletedCount++;
                $this->line("Deleted: {$student->first_name} {$student->last_name} (ID: {$student->id})");
            } catch (\Exception $e) {
                $this->error("Failed to delete student ID {$student->id}: " . $e->getMessage());
            }
        }

        $this->info("Successfully deleted {$deletedCount} student(s) created on {$date->format('Y-m-d')}");

        if ($totalRegistrations > 0) {
            $this->info("Related registrations were automatically cascade deleted");
        }

        return 0;
    }
}
