<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * Order matters: Organizer must exist before Admin and Staff are created.
     */
    public function run(): void
    {
        $this->call([
            OrganizerSeeder::class,
            AdminSeeder::class,
            StaffSeeder::class,
        ]);
    }
}
