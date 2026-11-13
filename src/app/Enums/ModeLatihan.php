<?php

namespace App\Enums;

/** Mode latihan (UC-03). Transliterasi ditambahkan untuk KF23 dan dicatat di B.1. */
enum ModeLatihan: string
{
    case Menulis = 'menulis';
    case Bunyi = 'bunyi';
    case Merangkai = 'merangkai';
    case Transliterasi = 'transliterasi';

    /** Daftar semua nilai, dipakai untuk validasi dan batasan CHECK. @return list<string> */
    public static function nilai(): array
    {
        return array_column(self::cases(), 'value');
    }
}
