<?php

namespace Database\Seeders;

use App\Models\Amenity;
use Illuminate\Database\Seeder;

class AmenitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $amenitiesData = [
            ['name' => 'Basseyn (Qishki/Yozgi)', 'icon' => '🏊‍♂️'],
            ['name' => 'Fin saunasi / Hammom', 'icon' => '🧖‍♂️'],
            ['name' => 'Bilyard stoli', 'icon' => '🎱'],
            ['name' => 'Stol tennisi', 'icon' => '🏓'],
            ['name' => 'Playstation 5', 'icon' => '🎮'],
            ['name' => 'Professional Karaoke', 'icon' => '🎤'],
            ['name' => 'Tezkor WiFi Internet', 'icon' => '📶'],
            ['name' => 'Mangal / O\'choq / Qozon', 'icon' => '🥩'],
            ['name' => 'Soya-salqin Tapchan', 'icon' => '🛋️'],
            ['name' => 'Bolalar maydonchasi', 'icon' => '🎠'],
            ['name' => 'Tog\' manzarasi', 'icon' => '🏔️'],
            ['name' => 'Konditsioner', 'icon' => '❄️'],
        ];

        foreach ($amenitiesData as $a) {
            Amenity::firstOrCreate(['name' => $a['name']], $a);
        }
    }
}
