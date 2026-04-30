<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Organizer;
use App\Models\Staff;
use Illuminate\Database\Seeder;

class StaffSeeder extends Seeder
{
    public function run(): void
    {
        $organizer = Organizer::where('slug', 'edaran-event-hub-corp')->firstOrFail();

        $dummyStaff = [
            ['name' => 'Alex',    'email' => 'alex@example.com',    'phone' => '+60123456789'],
            ['name' => 'Bob',   'email' => 'bob@example.com',   'phone' => '+6019876543'],
            ['name' => 'Cynthia',  'email' => 'cynthia@example.com',  'phone' => '+60134567890'],
        ];

        foreach ($dummyStaff as $data) {
            Staff::firstOrCreate(
                [
                    'organizer_id' => $organizer->id,
                    'email'        => $data['email'],
                ],
                [
                    'name'  => $data['name'],
                    'phone' => $data['phone'],
                ],
            );
        }
    }
}
