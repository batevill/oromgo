<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Owner User
        User::firstOrCreate(
            ['phone' => '+998901234567'],
            [
                'name' => 'Alisher Rahimov (Dacha Egasi)',
                'email' => 'owner@oromgo.uz',
                'role' => 'owner',
                'avatar' => 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=150&auto=format&fit=crop&q=80',
            ]
        );

        // 2. Guest Users for Reviews
        User::firstOrCreate(
            ['phone' => '+998911112233'],
            [
                'name' => 'Jasur Bekmurodov',
                'email' => 'jasur@example.com',
                'role' => 'user',
                'avatar' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=150&auto=format&fit=crop&q=80',
            ]
        );

        User::firstOrCreate(
            ['phone' => '+998934445566'],
            [
                'name' => 'Madina Usmonova',
                'email' => 'madina@example.com',
                'role' => 'user',
                'avatar' => 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=150&auto=format&fit=crop&q=80',
            ]
        );
    }
}
