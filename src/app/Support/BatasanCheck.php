<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

/**
 * Pembantu untuk menambahkan batasan CHECK pada tabel.
 *
 * Schema builder Laravel belum menyediakan CHECK secara langsung, jadi batasan
 * ditambahkan lewat SQL mentah. Batasan hanya dipasang pada MySQL (basis data
 * utama sesuai DPPL). Pada SQLite (dipakai saat pengujian otomatis) langkah ini
 * dilewati karena SQLite tidak mendukung ALTER TABLE ... ADD CONSTRAINT.
 */
class BatasanCheck
{
    /**
     * Menambahkan satu batasan CHECK.
     *
     * @param  string  $tabel  nama tabel
     * @param  string  $nama  nama batasan, misalnya chk_riwayat_skor
     * @param  string  $ekspresi  ekspresi SQL, misalnya "skor BETWEEN 0 AND 100"
     */
    public static function tambah(string $tabel, string $nama, string $ekspresi): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE `{$tabel}` ADD CONSTRAINT `{$nama}` CHECK ({$ekspresi})");
    }

    /**
     * Membuat ekspresi "kolom IN ('a','b')" dari daftar nilai.
     */
    public static function salahSatu(string $kolom, array $nilai, bool $bolehNull = false): string
    {
        $daftar = implode(', ', array_map(fn ($v) => "'".$v."'", $nilai));
        $ekspresi = "`{$kolom}` IN ({$daftar})";

        return $bolehNull ? "({$ekspresi} OR `{$kolom}` IS NULL)" : $ekspresi;
    }
}
