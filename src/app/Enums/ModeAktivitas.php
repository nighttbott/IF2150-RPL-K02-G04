<?php

namespace App\Enums;

/**
 * Jenis aktivitas pada riwayat_latihan. Pengumpulan dipakai modul tugas (PengumpulanTugas).
 *
 * Nilai string adalah nilai yang disimpan di basis data (huruf kecil) dan dikunci
 * dengan batasan CHECK pada MySQL. Jangan ubah tanpa kesepakatan tim.
 */
enum ModeAktivitas: string
{
    case Latihan = 'latihan';
    case Materi = 'materi';
    case Tugas = 'tugas';
    case Pengumpulan = 'pengumpulan';

    /**
     * Daftar semua nilai, dipakai untuk validasi dan batasan CHECK.
     *
     * @return list<string>
     */
    public static function nilai(): array
    {
        return array_column(self::cases(), 'value');
    }
}
