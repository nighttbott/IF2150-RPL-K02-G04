<?php

namespace App\Enums;

/**
 * Status ketepatan pengumpulan tugas (dipakai PenyelesaianTugas).
 *
 * Nilai string adalah nilai yang disimpan di basis data (huruf kecil) dan dikunci
 * dengan batasan CHECK pada MySQL. Jangan ubah tanpa kesepakatan tim.
 */
enum StatusKetepatan: string
{
    case TepatWaktu = 'tepat_waktu';
    case Terlambat = 'terlambat';

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
