<?php

namespace Database\Seeders;

use App\Enums\Peran;
use App\Models\AkunPengguna;
use App\Models\MotivasiBelajar;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Akun uji untuk tiga peran. Kata sandi semua akun: Rahasia123!
 * Data fiktif, jangan dipakai di lingkungan produksi.
 */
class AkunUjiSeeder extends Seeder
{
    /** email => [username, nama tampilan, peran] */
    public const AKUN = [
        'pelajar@ngaksara.test' => ['sari', 'Sari', Peran::Pelajar],
        'budi@ngaksara.test' => ['budi', 'Budi', Peran::Pelajar],
        'wulan@ngaksara.test' => ['wulan', 'Wulan', Peran::Pelajar],
        'raka@ngaksara.test' => ['raka', 'Raka', Peran::Pelajar],
        'pengajar@ngaksara.test' => ['bu_rina', 'Bu Rina', Peran::Pengajar],
        'pengajar2@ngaksara.test' => ['pak_dimas', 'Pak Dimas', Peran::Pengajar],
        'materi@ngaksara.test' => ['mas_bayu', 'Mas Bayu', Peran::TimMateri],
    ];

    public function run(): void
    {
        $hash = Hash::make('Rahasia123!');

        foreach (self::AKUN as $email => [$username, $nama, $peran]) {
            $akun = AkunPengguna::query()->updateOrCreate(
                ['email' => $email],
                ['username' => $username, 'password_hash' => $hash, 'peran' => $peran->value, 'nama_tampilan' => $nama],
            );

            // Setiap Pelajar memiliki satu baris motivasi, sama seperti saat mendaftar (Algo-001)
            if ($peran === Peran::Pelajar) {
                MotivasiBelajar::milikPelajar($akun->id_akun);
            }
        }
    }
}
