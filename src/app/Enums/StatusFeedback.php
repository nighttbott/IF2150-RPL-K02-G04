<?php

namespace App\Enums;

/** Status tindak lanjut feedback. Nilai string adalah nilai yang disimpan di basis data (huruf kecil) dan dikunci dengan batasan CHECK pada MySQL. */
enum StatusFeedback: string
{
    case Baru = 'baru';
    case Ditinjau = 'ditinjau';
    case Selesai = 'selesai';

    /** Daftar semua nilai, dipakai untuk validasi dan batasan CHECK. @return list<string> */
    public static function nilai(): array
    {
        return array_column(self::cases(), 'value');
    }
}
