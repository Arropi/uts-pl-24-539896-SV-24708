<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@coraltracker.id'],
            [
                'name' => 'Peneliti Terumbu Karang',
                'password' => Hash::make('password123'),
            ]
        );

        $this->call([
            CoralColonySeeder::class,
        ]);
    }
}
