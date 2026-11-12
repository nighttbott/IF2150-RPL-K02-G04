<?php

namespace App\Support;

final class HasilOperasi
{
    private function __construct(
        public readonly bool $berhasil,
        public readonly ?string $kode = null,
        public readonly mixed $pesan = null,
        public readonly array $data = [],
    ) {}

    public static function sukses(array $data = []): self
    {
        return new self(true, null, null, $data);
    }

    public static function gagal(string $kode, mixed $pesan = null): self
    {
        return new self(false, $kode, $pesan);
    }
}