<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Seeder utama: php artisan migrate --seed  atau  php artisan db:seed
 *
 * Seluruh data bersifat fiktif. Dataset profiling (100 Pelajar, 10.000 riwayat) tidak ikut di sini;
 * jalankan terpisah: php artisan db:seed --class=ProfilingSeeder
 */
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
