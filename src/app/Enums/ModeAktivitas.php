<?php

namespace App\Enums;

/** Jenis aktivitas pada riwayat_latihan. Pengumpulan dipakai modul tugas (PengumpulanTugas). */
enum ModeAktivitas: string
{
    case Latihan = 'latihan';
    case Materi = 'materi';
    case Tugas = 'tugas';
    case Pengumpulan = 'pengumpulan';

    /** Daftar semua nilai, dipakai untuk validasi dan batasan CHECK. @return list<string> */
    public static function nilai(): array
    {
        return array_column(self::cases(), 'value');
    }
}
