<?php

namespace App\Enums;

/** Kategori feedback (I-20). Materi juga dipakai umpan balik Pengajar (Algo-080). */
enum KategoriFeedback: string
{
    case Performa = 'performa';
    case Tampilan = 'tampilan';
    case Fitur = 'fitur';
    case Materi = 'materi';

    /** Daftar semua nilai, dipakai untuk validasi dan batasan CHECK. @return list<string> */
    public static function nilai(): array
    {
        return array_column(self::cases(), 'value');
    }
}
