<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Organizer;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class OrganizerSeeder extends Seeder
{
    public function run(): void
    {
        Organizer::firstOrCreate(
            ['slug' => 'edaran-event-hub-corp'],
            [
                'id'   => (string) Str::uuid(),
                'name' => 'Edaran Event Hub Corp',
            ],
        );
    }
}
