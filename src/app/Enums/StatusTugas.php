<?php

namespace App\Enums;

/** Status tugas kelas (dipakai TugasKelas). Nilai string adalah nilai yang disimpan di basis data (huruf kecil) dan dikunci dengan batasan CHECK pada MySQL. */
enum StatusTugas: string
{
    case Draf = 'draf';
    case Terbit = 'terbit';

    /** Daftar semua nilai, dipakai untuk validasi dan batasan CHECK. @return list<string> */
    public static function nilai(): array
    {
        return array_column(self::cases(), 'value');
    }
}
