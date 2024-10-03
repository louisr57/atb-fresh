<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Registration;

class RegistrationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Seed the RegistrationFactory

        // Create registrations one by one
        for ($i = 0; $i < 200; $i++) {
            Registration::factory()->create();

            // echo "Iteration: $i \n";
            // echo "Press the spacebar (or any key) to continue...\n";

            // // Wait for any keyboard input (requires hitting enter afterward in most terminals)
            // fgets(STDIN);

            // Proceed with the next iteration
        }

        // Registration::factory()->count(1)->create(); // Adjust the count as needed
    }
}
