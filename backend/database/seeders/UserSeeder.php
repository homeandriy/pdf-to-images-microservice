<?php

namespace Database\Seeders;


use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    const ADMIN_EMAIL = 'homeandriy@gmail.com';

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if ( ! User::where('email', self::ADMIN_EMAIL)->exists()) {
            User::factory()->withEmail(self::ADMIN_EMAIL)->create();
        }
        User::factory(5)->create();
    }
}
