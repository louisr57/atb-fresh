<?php

namespace Database\Seeders;

use App\Models\Student;
use App\Models\Event;
use App\Models\Venue;
use App\Models\Registration;
use Illuminate\Support\Facades\Hash;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    protected static ?string $password;

    public function run(): void
    {
        $faker = Faker::create();

        // Create students first
        $this->command->info('Creating students...');
        Student::factory(100)->create();

        // Create users
        $this->command->info('Creating users...');
        DB::table('users')->insert([
            [
                'name' => 'Aloka',
                'email' => 'alokamariona@gmail.com',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Joan',
                'email' => 'joan@auroville.org.in',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Louisr57',
                'email' => 'louisr57@gmail.com',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Rosa',
                'email' => 'nuatbconrosa@gmail.com',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Karin',
                'email' => 'awarenessinfo@gmail.com',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Amir',
                'email' => 'connect@atbwithamir.com',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Nicolas',
                'email' => 'nicovoul@hotmail.com',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Sebastien',
                'email' => 'sebrb@hotmail.com',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Valerie',
                'email' => 'laville.valerie@free.fr',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Celeste',
                'email' => 'edesmariaceleste@gmail.com',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Ignasi',
                'email' => 'ig.educacio22@gmail.com',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Lison',
                'email' => 'moeglelison@gmail.com',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Suryamayi',
                'email' => 'suryamayi@auroville.org.in',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // Create courses
        $this->command->info('Creating courses...');
        DB::table('courses')->insert([
            [
                'course_code' => 'ATB K-BT',
                'course_title' => 'Kindergarten/Basic Tools Module',
                'description' => 'Kindergarten/Basic Tools Module',
                'prerequisites' => 'none',
                'duration' => '1.0'
            ],
            [
                'course_code' => 'ATB 1',
                'course_title' => 'Introductory Basic Module',
                'description' => 'Introductory Basic Module',
                'prerequisites' => 'none',
                'duration' => '2.0'
            ],
            [
                'course_code' => 'ATB 2',
                'course_title' => 'Basic Module 2',
                'description' => 'Basic Module 2',
                'prerequisites' => 'ATB 1',
                'duration' => '3.0'
            ],
            [
                'course_code' => 'ATB 3',
                'course_title' => 'Basic Module 3',
                'description' => 'Basic Module 3',
                'prerequisites' => 'ATB 2',
                'duration' => '3.0'
            ],
            [
                'course_code' => 'ATB 4',
                'course_title' => 'Advanced Module 1',
                'description' => 'Advanced Module',
                'prerequisites' => 'All ATB Basic Modules',
                'duration' => '4.0'
            ],
            [
                'course_code' => 'ATB 5',
                'course_title' => 'Advanced Module 2',
                'description' => 'Advanced Module',
                'prerequisites' => 'All ATB Basic Modules',
                'duration' => '5.0'
            ],
            [
                'course_code' => 'M_Body',
                'course_title' => 'Embodiment Module',
                'description' => 'Embodiment Module',
                'prerequisites' => 'none',
                'duration' => '2.0'
            ],
        ]);

        // Create facilitators
        $this->command->info('Creating facilitators...');
        DB::table('facilitators')->insert([
            [
                'first_name' => 'Aloka',
                'last_name' => 'Marti',
                'gender' => 'F',
                'email' => 'alokamariona@gmail.com',
                'phone_number' => '+91 9843985445',
                'address' => 'Auroville',
                'city' => 'Auroville',
                'state' => 'Tamil Nadu',
                'country' => 'India/Catalunya',
                'post_code' => '605101',
                'website' => $faker->url(),
                'dob' => $faker->dateTimeBetween('-70 years', '-20 years'),
            ],
            [
                'first_name' => 'Joan',
                'last_name' => 'Sala',
                'gender' => 'M',
                'email' => 'joan@auroville.org.in',
                'phone_number' => '+91 8098144726',
                'address' => 'Auroville',
                'city' => 'Auroville',
                'state' => 'Tamil Nadu',
                'country' => 'India/Catalunya',
                'post_code' => '605101',
                'website' => 'https://www.joansala.com',
                'dob' => $faker->dateTimeBetween($startDate = '-70 years', $endDate = '-20 years', $timezone = null),
            ],
            [
                'first_name' => 'Rosa',
                'last_name' => 'Aleman',
                'gender' => 'F',
                'email' => 'nuatbconrosa@gmail.com',
                'phone_number' => '+91 7094299924',
                'address' => 'Auroville',
                'city' => 'Auroville',
                'state' => 'Tamil Nadu',
                'country' => 'India/South America',
                'post_code' => '605101',
                'website' => 'https://www.rosaaleman.com',
                'dob' => $faker->dateTimeBetween($startDate = '-70 years', $endDate = '-20 years', $timezone = null),
            ],
            [
                'first_name' => 'Karin',
                'last_name' => 'Van des Plass',
                'gender' => 'F',
                'email' => 'awarenessinfo@gmail.com',
                'phone_number' => '+31 637446469',
                'address' => 'Soest',
                'city' => 'Soest',
                'state' => null,
                'country' => 'Holland',
                'post_code' => null,
                'website' => $faker->url(),
                'dob' => $faker->dateTimeBetween($startDate = '-70 years', $endDate = '-20 years', $timezone = null),
            ],
            [
                'first_name' => 'Amir',
                'last_name' => 'Azulay',
                'gender' => 'M',
                'email' => 'connect@atbwithamir.com',
                'phone_number' => '+91 9751257709',
                'address' => 'Auroville',
                'city' => 'Auroville',
                'state' => 'Tamil Nadu',
                'country' => 'India/Israël',
                'post_code' => '605101',
                'website' => $faker->url(),
                'dob' => $faker->dateTimeBetween($startDate = '-70 years', $endDate = '-20 years', $timezone = null),
            ],
            [
                'first_name' => 'Nicolas',
                'last_name' => 'Maraval',
                'gender' => 'M',
                'email' => 'nicovoul@hotmail.com',
                'phone_number' => '+33 603594410',
                'address' => 'Castres',
                'city' => 'Castres',
                'state' => null,
                'country' => 'France',
                'post_code' => '605101',
                'website' => $faker->url(),
                'dob' => $faker->dateTimeBetween($startDate = '-70 years', $endDate = '-20 years', $timezone = null),
            ],
            [
                'first_name' => 'Sébastien',
                'last_name' => 'Rabbé',
                'gender' => 'M',
                'email' => 'sebrb@hotmail.com',
                'phone_number' => '+33 687776566',
                'address' => 'Paris',
                'city' => 'Paris',
                'state' => null,
                'country' => 'France',
                'post_code' => null,
                'website' => $faker->url(),
                'dob' => $faker->dateTimeBetween($startDate = '-70 years', $endDate = '-20 years', $timezone = null),
            ],
            [
                'first_name' => 'Valérie',
                'last_name' => 'Laville',
                'gender' => 'F',
                'email' => 'laville.valerie@free.fr',
                'phone_number' => '+33 675847558',
                'address' => 'Brest',
                'city' => 'Brest',
                'state' => null,
                'country' => 'France',
                'post_code' => null,
                'website' => $faker->url(),
                'dob' => $faker->dateTimeBetween($startDate = '-70 years', $endDate = '-20 years', $timezone = null),
            ],
            [
                'first_name' => 'Celeste',
                'last_name' => 'Ledesma',
                'gender' => 'F',
                'email' => 'edesmariaceleste@gmail.com',
                'phone_number' => '+54 2804589125',
                'address' => 'Puerto Madryn',
                'city' => 'Puerto Madryn',
                'state' => null,
                'country' => 'Argentina',
                'post_code' => null,
                'website' => $faker->url(),
                'dob' => $faker->dateTimeBetween($startDate = '-70 years', $endDate = '-20 years', $timezone = null),
            ],
            [
                'first_name' => 'Ignasi',
                'last_name' => 'Salvatella',
                'gender' => 'M',
                'email' => 'ig.educacio22@gmail.com',
                'phone_number' => '+34 665804833',
                'address' => 'Barcelona',
                'city' => 'Barcelona',
                'state' => null,
                'country' => 'Catalunya',
                'post_code' => null,
                'website' => $faker->url(),
                'dob' => $faker->dateTimeBetween($startDate = '-70 years', $endDate = '-20 years', $timezone = null),
            ],
            [
                'first_name' => 'Lison',
                'last_name' => 'Moegle',
                'gender' => 'F',
                'email' => 'moeglelison@gmail.com',
                'phone_number' => null,
                'address' => 'Grace',
                'city' => 'Auroville',
                'state' => null,
                'country' => 'France/India',
                'post_code' => null,
                'website' => $faker->url(),
                'dob' => $faker->dateTimeBetween($startDate = '-70 years', $endDate = '-20 years', $timezone = null),
            ],
            [
                'first_name' => 'Suryamayi',
                'last_name' => 'Ashwini',
                'gender' => 'F',
                'email' => 'suryamayi@auroville.org.in',
                'phone_number' => '+91 9489457158',
                'address' => 'Auroville',
                'city' => 'Auroville',
                'state' => 'Tamil Nadu',
                'country' => 'India',
                'post_code' => null,
                'website' => $faker->url(),
                'dob' => $faker->dateTimeBetween($startDate = '-70 years', $endDate = '-20 years', $timezone = null),
            ]
        ]);

        // Create venues first since events depend on them
        Venue::factory(100)->create();

        $this->command->info('Creating events...');
        Event::factory(300)->create();

        // Create registrations in small batches with progress tracking
        $this->command->info('Creating registrations...');
        $batchSize = 10;
        $totalRegistrations = 100;
        $batches = ceil($totalRegistrations / $batchSize);

        $bar = $this->command->getOutput()->createProgressBar($batches);
        $bar->start();

        for ($i = 0; $i < $batches; $i++) {
            $size = min($batchSize, $totalRegistrations - ($i * $batchSize));

            try {
                Registration::factory()->count($size)->create();
            } catch (\Exception $e) {
                $this->command->error("Error in batch $i: " . $e->getMessage());
                continue;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->command->info("\nRegistrations created successfully");

        // Update participant counts efficiently
        $this->command->info('Updating participant counts...');
        DB::statement('
            UPDATE events e
            SET participant_count = (
                SELECT COUNT(*)
                FROM registrations r
                WHERE r.event_id = e.id
            )
        ');

        $this->command->info('Database seeding completed successfully');
    }
}
