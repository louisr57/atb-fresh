<?php

namespace App\Console\Commands;

use App\Models\Student;
use App\Models\User;
use Illuminate\Console\Command;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\ToArray;
use Spatie\Activitylog\Models\Activity;
use Carbon\Carbon;

class ImportStudentsFromExcel extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'students:import {filename} {--user= : User name to attribute the import to} {--dryrun : Run without making database changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import students from Excel file located in storage/imports directory';

    /**
     * Column mappings for flexible header detection
     */
    private $columnMappings = [
        'first_name' => ['first_name', 'firstname', 'first name', 'first-name', 'fname'],
        'last_name' => ['last_name', 'lastname', 'last name', 'last-name', 'lname', 'surname'],
        'email' => ['email', 'email_address', 'email address', 'e-mail', 'e_mail'],
    ];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $filename = $this->argument('filename');
        $isDryRun = $this->option('dryrun');
        $userName = $this->option('user');

        // Resolve the user if provided
        $user = null;
        if ($userName) {
            $user = $this->resolveUser($userName);
            if (!$user) {
                return 1; // Error already displayed in resolveUser method
            }
        }

        if ($isDryRun) {
            $this->info('DRY RUN MODE - No changes will be made');
            $this->newLine();
        }

        if ($user) {
            $this->info("Import will be attributed to user: {$user->name}");
            $this->newLine();
        }

        $filePath = storage_path('app/imports/' . $filename);

        // Check if file exists
        if (!file_exists($filePath)) {
            $this->error("File not found: {$filePath}");
            $this->info("Please place your Excel file in the storage/app/imports/ directory");
            return 1;
        }

        try {
            // Read Excel file
            $this->info("Processing {$filename}...");
            $data = Excel::toArray(new class implements ToArray {
                public function array(array $array): array
                {
                    return $array;
                }
            }, $filePath);

            if (empty($data) || empty($data[0])) {
                $this->error('Excel file is empty or invalid');
                return 1;
            }

            $rows = $data[0]; // Get first sheet
            if (empty($rows)) {
                $this->error('No data found in Excel file');
                return 1;
            }

            // Detect column indices
            $headers = array_map('strtolower', array_map('trim', $rows[0]));
            $columnIndices = $this->detectColumns($headers);

            if (!$columnIndices) {
                return 1;
            }

            $this->info("✓ Found columns: " . implode(', ', [
                $rows[0][$columnIndices['first_name']],
                $rows[0][$columnIndices['last_name']],
                $rows[0][$columnIndices['email']]
            ]));

            // Process data rows (skip header)
            $dataRows = array_slice($rows, 1);
            $results = $this->processRows($dataRows, $columnIndices, $isDryRun, $user);

            // Generate duplicates file
            if (!empty($results['duplicates'])) {
                $this->generateDuplicatesFile($results['duplicates'], $isDryRun);
            }

            // Display summary
            $this->displaySummary($results, $isDryRun);

            return 0;

        } catch (\Exception $e) {
            $this->error("Error processing file: " . $e->getMessage());
            return 1;
        }
    }

    /**
     * Detect column indices from headers
     */
    private function detectColumns(array $headers): ?array
    {
        $indices = [];

        foreach ($this->columnMappings as $field => $variations) {
            $found = false;
            foreach ($variations as $variation) {
                $index = array_search(strtolower($variation), $headers);
                if ($index !== false) {
                    $indices[$field] = $index;
                    $found = true;
                    break;
                }
            }

            if (!$found) {
                $this->error("Required column '{$field}' not found. Looking for variations: " . implode(', ', $variations));
                return null;
            }
        }

        return $indices;
    }

    /**
     * Validate email format according to internet standards
     */
    private function isValidEmail(string $email): bool
    {
        // Use PHP's built-in email validation which follows RFC standards
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        // Additional checks for common malformed emails
        // Check for basic structure: local@domain
        if (substr_count($email, '@') !== 1) {
            return false;
        }

        $parts = explode('@', $email);
        $local = $parts[0];
        $domain = $parts[1];

        // Local part should not be empty and should not start/end with dots
        if (empty($local) || $local[0] === '.' || substr($local, -1) === '.') {
            return false;
        }

        // Domain part should not be empty and should contain at least one dot
        if (empty($domain) || strpos($domain, '.') === false) {
            return false;
        }

        // Domain should not start or end with dots or hyphens
        if ($domain[0] === '.' || substr($domain, -1) === '.' ||
            $domain[0] === '-' || substr($domain, -1) === '-') {
            return false;
        }

        return true;
    }

    /**
     * Resolve user by name
     */
    private function resolveUser(string $userName): ?User
    {
        $user = User::where('name', $userName)->first();

        if (!$user) {
            $this->error("User '{$userName}' not found in the database.");
            $this->info("Available users:");
            $availableUsers = User::orderBy('name')->pluck('name')->toArray();
            foreach ($availableUsers as $availableUser) {
                $this->line("  - {$availableUser}");
            }
            return null;
        }

        return $user;
    }

    /**
     * Process data rows
     */
    private function processRows(array $rows, array $columnIndices, bool $isDryRun, ?User $user = null): array
    {
        $created = 0;
        $duplicates = [];
        $errors = 0;

        foreach ($rows as $rowIndex => $row) {
            $actualRowNumber = $rowIndex + 2; // +2 because we skipped header and array is 0-indexed

            try {
                // Extract data
                $firstName = trim($row[$columnIndices['first_name']] ?? '');
                $lastName = trim($row[$columnIndices['last_name']] ?? '');
                $email = trim($row[$columnIndices['email']] ?? '');

                // Validate required fields
                if (empty($firstName) || empty($lastName) || empty($email)) {
                    $this->warn("⚠ Row {$actualRowNumber}: Skipped - missing required data");
                    $errors++;
                    continue;
                }

                // Validate email format - add to duplicates file if invalid
                if (!$this->isValidEmail($email)) {
                    $duplicates[] = [
                        'row' => $actualRowNumber,
                        'first_name' => $firstName,
                        'last_name' => $lastName,
                        'email' => $email,
                        'reason' => 'bad email'
                    ];
                    $this->warn("⚠ Row {$actualRowNumber}: Skipped - invalid email format: {$email}");
                    $errors++;
                    continue;
                }

                // Check for duplicate email
                if (Student::where('email', $email)->exists()) {
                    $duplicates[] = [
                        'row' => $actualRowNumber,
                        'first_name' => $firstName,
                        'last_name' => $lastName,
                        'email' => $email,
                        'reason' => 'duplicate email'
                    ];
                    $this->warn("⚠ Row {$actualRowNumber}: Skipped duplicate email {$email}");
                    continue;
                }

                // Create student (if not dry run)
                if (!$isDryRun) {
                    $student = Student::create([
                        'first_name' => $firstName,
                        'last_name' => $lastName,
                        'email' => $email,
                        'address' => 'unknown',
                        'city' => 'unknown',
                        'state' => 'unknown',
                        'country' => 'unknown',
                        'post_code' => 'unknown',
                    ]);

                    // Update the automatic activity log entry created by the Student model
                    // to have the correct causer if a user was specified
                    if ($user) {
                        // Find the most recent activity log entry for this student (the "created" entry)
                        $latestActivity = Activity::where('subject_type', Student::class)
                            ->where('subject_id', $student->id)
                            ->where('description', 'like', '%created%')
                            ->latest()
                            ->first();

                        if ($latestActivity) {
                            $latestActivity->update([
                                'causer_type' => User::class,
                                'causer_id' => $user->id
                            ]);
                        }
                    }

                    // Create additional activity log entry for the import process
                    $activityLogger = activity('student_import');

                    if ($user) {
                        $activityLogger->causedBy($user);
                    }

                    $activityLogger
                        ->performedOn($student)
                        ->log("Student imported from Excel file");

                    $userInfo = $user ? " by {$user->name}" : "";
                    $this->info("✓ Row {$actualRowNumber}: Created student {$firstName} {$lastName} ({$email}){$userInfo}");
                } else {
                    $userInfo = $user ? " (would be attributed to {$user->name})" : "";
                    $this->info("✓ Row {$actualRowNumber}: Would create student {$firstName} {$lastName} ({$email}){$userInfo}");
                }

                $created++;

            } catch (\Exception $e) {
                $this->error("✗ Row {$actualRowNumber}: Error - " . $e->getMessage());
                $errors++;
            }
        }

        return [
            'total' => count($rows),
            'created' => $created,
            'duplicates' => $duplicates,
            'errors' => $errors
        ];
    }

    /**
     * Generate duplicates and rejects file
     */
    private function generateDuplicatesFile(array $duplicates, bool $isDryRun): string
    {
        $timestamp = Carbon::now()->format('Ymd_His');
        $filename = "duplicates_{$timestamp}.txt";
        $filePath = storage_path('app/imports/' . $filename);

        $content = "Duplicate Email Records and Rejects Found\n";
        $content .= "Generated: " . Carbon::now()->format('Y-m-d H:i:s') . "\n";
        $content .= str_repeat("=", 60) . "\n\n";

        foreach ($duplicates as $duplicate) {
            $reason = isset($duplicate['reason']) ? " ({$duplicate['reason']})" : "";
            $content .= "Row {$duplicate['row']}: {$duplicate['first_name']}, {$duplicate['last_name']}, {$duplicate['email']}{$reason}\n";
        }

        if (!$isDryRun) {
            file_put_contents($filePath, $content);
            $this->info("Duplicates/Rejects file created: storage/app/imports/{$filename}");
        } else {
            $this->info("Duplicates/Rejects file would be created at: storage/app/imports/{$filename}");
            $this->newLine();
            $this->info("Preview of duplicates/rejects content:");
            $previewLines = array_slice(explode("\n", $content), 0, 10);
            foreach ($previewLines as $line) {
                if (!empty(trim($line))) {
                    $this->line($line);
                }
            }
            if (count($duplicates) > 7) { // Account for header lines
                $this->line("... and " . (count($duplicates) - 7) . " more duplicates/rejects");
            }
        }

        return $filename;
    }

    /**
     * Display summary
     */
    private function displaySummary(array $results, bool $isDryRun): void
    {
        $this->newLine();
        $prefix = $isDryRun ? 'Summary (DRY RUN):' : 'Summary:';
        $this->info($prefix);

        $createdText = $isDryRun ? 'Students that would be created' : 'Students created';
        $duplicatesText = $isDryRun ? 'Duplicates/Rejects that would be found' : 'Duplicates/Rejects found';

        $this->line("- Total rows processed: {$results['total']}");
        $this->line("- {$createdText}: {$results['created']}");
        $this->line("- {$duplicatesText}: " . count($results['duplicates']));

        if ($results['errors'] > 0) {
            $this->line("- Errors/Skipped: {$results['errors']}");
        }

        if (!empty($results['duplicates']) && !$isDryRun) {
            $timestamp = Carbon::now()->format('Ymd_His');
            $this->line("- Duplicates/Rejects file: storage/app/imports/duplicates_{$timestamp}.txt");
        }
    }
}
