<?php

namespace App\Enums;

/** Status satu baris riwayat_latihan: berhasil/perlu_diulang untuk latihan, selesai untuk aktivitas lain. */
enum StatusRiwayat: string
{
    case Berhasil = 'berhasil';
    case PerluDiulang = 'perlu_diulang';
    case Selesai = 'selesai';

    /** Daftar semua nilai, dipakai untuk validasi dan batasan CHECK. @return list<string> */
    public static function nilai(): array
    {
        return array_column(self::cases(), 'value');
    }
}
