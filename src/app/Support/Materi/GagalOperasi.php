<?php

namespace App\Support\Materi;

use RuntimeException;

/** Dilempar di dalam DB::transaction agar transaksi dibatalkan, lalu diubah kembali menjadi HasilOperasi. */
final class GagalOperasi extends RuntimeException
{
    public function __construct(public readonly HasilOperasi $hasil)
    {
        parent::__construct($hasil->pesan);
    }
}
