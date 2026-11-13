<?php

namespace App\Enums;

/** Status keanggotaan kelas (dipakai KeanggotaanKelas). Nilai string adalah nilai yang disimpan di basis data (huruf kecil) dan dikunci dengan batasan CHECK pada MySQL. */
enum StatusKeanggotaan: string
{
    case Aktif = 'aktif';
    case Keluar = 'keluar';
    case Nonaktif = 'nonaktif';

    /** Daftar semua nilai, dipakai untuk validasi dan batasan CHECK. @return list<string> */
    public static function nilai(): array
    {
        return array_column(self::cases(), 'value');
    }
}
