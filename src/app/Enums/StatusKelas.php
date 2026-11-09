<?php

namespace App\Enums;

/**
 * Status kelas belajar.
 *
 * Nilai string adalah nilai yang disimpan di basis data (huruf kecil) dan dikunci
 * dengan batasan CHECK pada MySQL. Jangan ubah tanpa kesepakatan tim.
 */
enum StatusKelas: string
{
    case Aktif = 'aktif';
    case Nonaktif = 'nonaktif';

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
