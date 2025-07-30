<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        \App\Models\User::updateOrCreate(
            ['email' => 'pkmalnkiya@gmail.com'],
            ['name' => 'PK Malnkya', 'password' => bcrypt('password@123')]
        );

        \App\Models\User::updateOrCreate(
            ['email' => 'admin@yopmail.com'],
            ['name' => 'Admin User', 'password' => bcrypt('password@123')]
        );

        $this->call([
            ContactSeeder::class,
        ]);
    }
}
