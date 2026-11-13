<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/** Seeder utama (php artisan migrate --seed); seluruh data bersifat fiktif. */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            AkunUjiSeeder::class,
            MateriSeeder::class,
            KelasContohSeeder::class,
            RiwayatContohSeeder::class,
        ]);
    }
}
