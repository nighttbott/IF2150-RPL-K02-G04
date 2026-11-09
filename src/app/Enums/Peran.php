<?php

namespace App\Enums;

/**
 * Peran pengguna. Nilai huruf kecil mengikuti kode modul materi dan kelas yang sudah digabung.
 *
 * Nilai string adalah nilai yang disimpan di basis data (huruf kecil) dan dikunci
 * dengan batasan CHECK pada MySQL. Jangan ubah tanpa kesepakatan tim.
 */
enum Peran: string
{
    case Pelajar = 'pelajar';
    case Pengajar = 'pengajar';
    case TimMateri = 'tim_materi';

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
