<?php

namespace App\Support\Materi;

/** Tipe "HasilBerkas" pada Tabel 4.1: lokasi berkas pada disk penyimpanan, bukan isi berkasnya. */
final class HasilBerkas
{
    private function __construct(
        public readonly bool $berhasil,
        public readonly string $kode,
        public readonly ?string $path = null,
    ) {}

    public static function sukses(string $path): self
    {
        return new self(true, 'OK', $path);
    }

    public static function gagal(string $kode): self
    {
        return new self(false, $kode);
    }
}
