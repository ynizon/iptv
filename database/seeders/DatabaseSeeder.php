<?php

namespace Database\Seeders;

use App\Models\User;
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
        'Bollywood',
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
        'CZECH Republic',
        'BRAZIL',
        'BULGARIA',
        'ARMENIA',
        'ALBANIA',
        'ALGERIA',
        'DENMARK',
        'KURDISTAN',
        'LATINO',
        'MEXICO',
        'MOROCCO',
        'NETHERLAND',
        'PAKISTAN',
        'NORWAY',
        'POLAND',
        'PORTUGAL',
        'ROMANIA',
        'RUSSIA & UKRAINE',
        'SPAIN',
        'SWEDEN',
        'TUNISIA',
        'TURKEY',
        'INDIA',
        'IRAN',
        'ISLAM',
        'ITALIA',
        'GERMANY',
        'GREECE',
        'HUNGARY',
        'BELGIUM',
        'CARAIBES',
        'EX-YU',
        'OSN'
    ];

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Admin',
            'admin' => true,
            'formats' => '["HD","UHD","FHD"]',
            'email' => env('ADMIN_USER','admin@admin.com'),
            'password'=>bcrypt(env('ADMIN_PASSWORD',"admin")),
        ]);

        DB::table('playlists')->insert([
            'name' => 'IPTV',
            'content' => '',
            'tld' => '',
            'ip' => '',
            'url' => env("M3U","http://yourwebsite/get.php?username=XXX&password=YYY&type=m3u_plus&output=ts.m3U"),
        ]);

        foreach ($this->filters as $filter)
        {
            DB::table('filters')->insert([
                'name' => $filter,
            ]);
        }
    }
}
