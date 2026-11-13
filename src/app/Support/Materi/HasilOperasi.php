<?php

namespace App\Support\Materi;

/** Tipe "HasilOperasi" pada Tabel 4.1 untuk modul materi. */
final class HasilOperasi
{
    private function __construct(
        public readonly bool $berhasil,
        public readonly string $kode,
        public readonly string $pesan,
        public readonly array $data = [],
    ) {}

    public static function sukses(array $data = [], string $pesan = 'Berhasil'): self
    {
        return new self(true, 'OK', $pesan, $data);
    }

    public static function gagal(string $kode, string $pesan): self
    {
        return new self(false, $kode, $pesan);
    }
}
