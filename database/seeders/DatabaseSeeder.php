<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    private array $filters = [
        'C+AF',
        'arab',
        'ANGLAISES',
        'AFRICA',
        'DZ/MA/TN',
        'ITALIANA',
        'ALLEMANDES',
        'BELGES',
        'RAMADAN',
        'CANADA',
        'Albanaises',
        'Grecques',
        'Tchéquie',
        'Roumaines',
        'INDE',
        'Armeniennes',
        'BOSNIAQUE',
        'RUSSES',
        'ETATS-UNIS',
        'SWITZERLAND',
        'ESPAGNOLES',
        'PORTUGAISES',
        'BRÈSILIENNES',
        'POLONAISES',
        'Scandinavie',
        'NETHERLANDS',
        'TURQUES',
        'MAGHREB',
        'AFRIQUE',
    ];

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        DB::table('playlists')->insert([
            'name' => 'IPTV',
            'url' => "http://*.m3U",
        ]);

        foreach ($this->filters as $filter)
        {
            DB::table('filters')->insert([
                'name' => $filter,
            ]);
        }
    }
}
