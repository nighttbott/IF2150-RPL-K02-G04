<?php

namespace Database\Factories;

use App\Enums\Peran;
use App\Models\AkunPengguna;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/** Pembuat data akun fiktif untuk seeder dan pengujian. @extends Factory<AkunPengguna> */
class AkunPenggunaFactory extends Factory
{
    protected $model = AkunPengguna::class;

    /** Hash disimpan sekali agar pembuatan banyak akun tidak lambat. */
    protected static ?string $hashSandi = null;

    public function definition(): array
    {
        return [
            'email' => fake()->unique()->safeEmail(),
            'username' => fake()->unique()->userName(),
            'password_hash' => static::$hashSandi ??= Hash::make('Rahasia123!'),
            'peran' => Peran::Pelajar->value,
            'nama_tampilan' => fake()->firstName(),
        ];
    }

    public function pengajar(): static
    {
        return $this->state(['peran' => Peran::Pengajar->value]);
    }

    public function timMateri(): static
    {
        return $this->state(['peran' => Peran::TimMateri->value]);
    }
}
