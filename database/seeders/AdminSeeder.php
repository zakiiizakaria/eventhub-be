<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Organizer;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $organizer = Organizer::where('slug', 'edaran-event-hub-corp')->firstOrFail();

        Admin::firstOrCreate(
            ['email' => 'admin@edaraneventhub.com'],
            [
                'organizer_id' => $organizer->id,
                'name'         => 'Super Admin',
                'password'     => Hash::make('password'),
            ],
        );
    }
}
