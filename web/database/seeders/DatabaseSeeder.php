<?php

namespace Database\Seeders;

use App\Models\Amenity;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $amenities = [
            ['name' => 'Basseyn (Qishki/Yozgi)', 'icon' => 'pool'],
            ['name' => 'Sauna / Turk hammomi', 'icon' => 'hot_tub'],
            ['name' => 'Bilyard', 'icon' => 'sports_bar'],
            ['name' => 'Stol tennisi', 'icon' => 'sports_tennis'],
            ['name' => 'Playstation 5', 'icon' => 'sports_esports'],
            ['name' => 'Karaoke', 'icon' => 'mic'],
            ['name' => 'WiFi Internet', 'icon' => 'wifi'],
            ['name' => 'Mangal / O\'choq / Qozon', 'icon' => 'outdoor_grill'],
            ['name' => 'Tapchan / Besedka', 'icon' => 'deck'],
            ['name' => 'Bolalar maydonchasi', 'icon' => 'child_care'],
        ];

        foreach ($amenities as $amenity) {
            Amenity::firstOrCreate(['name' => $amenity['name']], $amenity);
        }
    }
}
