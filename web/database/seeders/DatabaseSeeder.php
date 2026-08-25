<?php

namespace Database\Seeders;

use App\Models\Amenity;
use App\Models\Dacha;
use App\Models\DachaMedia;
use App\Models\Review;
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
        // 1. Amenities (Qulayliklar)
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

        $amenities = [];
        foreach ($amenitiesData as $a) {
            $amenities[] = Amenity::firstOrCreate(['name' => $a['name']], $a);
        }

        // 2. Owner User
        $owner = User::firstOrCreate(
            ['phone' => '+998901234567'],
            [
                'name' => 'Alisher Rahimov (Dacha Egasi)',
                'email' => 'owner@oromgo.uz',
                'role' => 'owner',
                'avatar' => 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=150&auto=format&fit=crop&q=80',
            ]
        );

        // 3. Guest Users for Reviews
        $guest1 = User::firstOrCreate(
            ['phone' => '+998911112233'],
            [
                'name' => 'Jasur Bekmurodov',
                'email' => 'jasur@example.com',
                'role' => 'user',
                'avatar' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=150&auto=format&fit=crop&q=80',
            ]
        );

        $guest2 = User::firstOrCreate(
            ['phone' => '+998934445566'],
            [
                'name' => 'Madina Usmonova',
                'email' => 'madina@example.com',
                'role' => 'user',
                'avatar' => 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=150&auto=format&fit=crop&q=80',
            ]
        );

        // 4. Demo Dachalar
        $dachasData = [
            [
                'name' => 'Chorvoq Mountain Panorama Dacha',
                'description' => 'Chorvoq suv omboriga to\'g\'ridan-to\'g\'ri qaragan premium dacha. Katta isitiladigan yozgi va qishki basseyn, 5 ta yotoqxona, keng mehmonxona, bilyard va karaoke bilan jihozlangan. Oila va do\'stlar bilan ajoyib dam olish uchun eng yaxshi tanlov.',
                'capacity' => 14,
                'rooms_count' => 5,
                'region' => 'Toshkent viloyati',
                'district' => 'Bo\'stonliq tumani',
                'mahalla' => 'Yusufxona',
                'address' => 'Chorvoq bo\'yi 42-uy',
                'latitude' => 41.6255,
                'longitude' => 69.9622,
                'weekday_price' => 180,
                'weekend_price' => 250,
                'currency' => 'USD',
                'status' => 'active',
                'amenities' => [1, 2, 3, 5, 6, 7, 8, 9, 11, 12],
                'images' => [
                    'https://images.unsplash.com/photo-1580587771525-78b9dba3b914?w=1200&auto=format&fit=crop&q=80',
                    'https://images.unsplash.com/photo-1512917774080-9991f1c4c750?w=1200&auto=format&fit=crop&q=80',
                    'https://images.unsplash.com/photo-1613977257363-707ba9348227?w=1200&auto=format&fit=crop&q=80',
                ],
                'reviews' => [
                    [
                        'user_id' => $guest1->id,
                        'rating' => 5,
                        'comment' => 'Dacha sharoitlari juda ajoyib! Ayniqsa basseyndagi suv toza va harorati qulay ekan. Manzara esa daryo va tog\'larga qaragan, unutilmas dam oldik.',
                    ],
                    [
                        'user_id' => $guest2->id,
                        'rating' => 5,
                        'comment' => 'Hamma narsa 5 yulduzli! Tozalik, sauna va karaoke juda yoqdi. Dacha egasi ham juda xushmuomala inson ekan.',
                    ],
                ]
            ],
            [
                'name' => 'Amirsoy Forest Eco Villa',
                'description' => 'Amirsoy tog\' kurorti yaqinidagi qalin archazorlar orasida joylashgan eko-dacha. Tabiiy yog\'ochdan qurilgan shinam kottej, issiq sauna, jakuzi, kamin va sokin tog\' havosi sizga unutilmas dam beradi.',
                'capacity' => 8,
                'rooms_count' => 3,
                'region' => 'Toshkent viloyati',
                'district' => 'Bo\'stonliq tumani',
                'mahalla' => 'Chimyon',
                'address' => 'Amirsoy yo\'li 12-uy',
                'latitude' => 41.5362,
                'longitude' => 70.0154,
                'weekday_price' => 140,
                'weekend_price' => 190,
                'currency' => 'USD',
                'status' => 'active',
                'amenities' => [2, 7, 8, 9, 11, 12],
                'images' => [
                    'https://images.unsplash.com/photo-1542314831-068cd1dbfeeb?w=1200&auto=format&fit=crop&q=80',
                    'https://images.unsplash.com/photo-1520250497591-112f2f40a3f4?w=1200&auto=format&fit=crop&q=80',
                ],
                'reviews' => [
                    [
                        'user_id' => $guest1->id,
                        'rating' => 5,
                        'comment' => 'Haqiqiy archazor va toza havo! Kamin yonida o\'tirish alohida rohat bag\'ishladi.',
                    ],
                ]
            ],
            [
                'name' => 'Bochka Royal Lake House',
                'description' => 'Chorvoq "Bochka" hududidagi qulay joylashuvga ega dacha. Katta filtrlangan basseyn, tapchanlar, stol tennisi va barcha oshpazlik anjomlari bilan to\'liq ta\'minlangan.',
                'capacity' => 12,
                'rooms_count' => 4,
                'region' => 'Toshkent viloyati',
                'district' => 'Bo\'stonliq tumani',
                'mahalla' => 'Sijjak',
                'address' => 'Bochka markazi 7-uy',
                'latitude' => 41.6512,
                'longitude' => 69.9845,
                'weekday_price' => 120,
                'weekend_price' => 170,
                'currency' => 'USD',
                'status' => 'active',
                'amenities' => [1, 4, 7, 8, 9, 12],
                'images' => [
                    'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?w=1200&auto=format&fit=crop&q=80',
                ],
                'reviews' => [
                    [
                        'user_id' => $guest2->id,
                        'rating' => 4,
                        'comment' => 'Bochka markaziga juda yaqin, hamma joyga borish oson. Basseyn yaxshi ishladi.',
                    ],
                ]
            ],
            [
                'name' => 'Parkent Soy Bo\'yi Shodlik Dacha',
                'description' => 'Shovullab oqayotgan tog\' soyi bo\'yidagi salqin va yashil dacha. Toshkentdan atigi 40 daqiqalik masofada. Katta oilaviy tadbirlar, hordiq chiqarish va ochiq havoda dam olish uchun juda qulay.',
                'capacity' => 16,
                'rooms_count' => 6,
                'region' => 'Toshkent viloyati',
                'district' => 'Parkent tumani',
                'mahalla' => 'Kumushkon',
                'address' => 'Soybo\'yi ko\'chasi 18',
                'latitude' => 41.2954,
                'longitude' => 69.6782,
                'weekday_price' => 1500000,
                'weekend_price' => 2200000,
                'currency' => 'UZS',
                'status' => 'active',
                'amenities' => [1, 3, 6, 7, 8, 9, 11],
                'images' => [
                    'https://images.unsplash.com/photo-1600585154526-990dced4db0d?w=1200&auto=format&fit=crop&q=80',
                ],
                'reviews' => []
            ],
            [
                'name' => 'Humson Green Oasis Villa',
                'description' => 'Humsondagi toza tog\' havosi va ajoyib tabiat quchog\'idagi zamonaviy villa. Smart TV, PS5, keng basseynga ega hovli va qulay sharoitlar.',
                'capacity' => 10,
                'rooms_count' => 4,
                'region' => 'Toshkent viloyati',
                'district' => 'Bo\'stonliq tumani',
                'mahalla' => 'Humson',
                'address' => 'Bog\'i shamol 5',
                'latitude' => 41.6834,
                'longitude' => 69.8921,
                'weekday_price' => 110,
                'weekend_price' => 160,
                'currency' => 'USD',
                'status' => 'active',
                'amenities' => [1, 5, 7, 8, 9, 12],
                'images' => [
                    'https://images.unsplash.com/photo-1518780664697-55e3ad937233?w=1200&auto=format&fit=crop&q=80',
                ],
                'reviews' => []
            ],
            [
                'name' => 'Zomin Tog\' Gavhari Kotteji',
                'description' => 'Jizzax viloyati Zomin milliy bog\'i yaqinidagi ajoyib archazor maskan. Arzon, qulay va toza sharoitga ega oilaviy kottej.',
                'capacity' => 8,
                'rooms_count' => 3,
                'region' => 'Jizzax viloyati',
                'district' => 'Zomin tumani',
                'mahalla' => 'O\'riklisoy',
                'address' => 'Zomin tog\' yo\'li 24',
                'latitude' => 39.9612,
                'longitude' => 68.3984,
                'weekday_price' => 800000,
                'weekend_price' => 1200000,
                'currency' => 'UZS',
                'status' => 'active',
                'amenities' => [7, 8, 9, 11],
                'images' => [
                    'https://images.unsplash.com/photo-1587061949409-02df41d5e562?w=1200&auto=format&fit=crop&q=80',
                ],
                'reviews' => []
            ]
        ];

        foreach ($dachasData as $data) {
            $images = $data['images'];
            $amenityIds = $data['amenities'];
            $reviews = $data['reviews'] ?? [];
            unset($data['images'], $data['amenities'], $data['reviews']);

            $data['user_id'] = $owner->id;
            $dacha = Dacha::create($data);

            // Amenities
            $dacha->amenities()->sync($amenityIds);

            // Media
            foreach ($images as $imgUrl) {
                $dacha->media()->create([
                    'type' => 'image',
                    'path' => $imgUrl,
                ]);
            }

            // Reviews
            foreach ($reviews as $rev) {
                $dacha->reviews()->create($rev);
            }
        }
    }
}
