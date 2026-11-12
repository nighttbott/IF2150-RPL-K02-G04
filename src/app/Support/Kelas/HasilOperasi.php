<?php

namespace App\Support\Kelas;

/** HasilOperasi DPPL untuk modul kelas; tidak bergantung DTO modul materi. */
final readonly class HasilOperasi
{
    /** @param array<string, mixed> $data */
    private function __construct(
        public bool $berhasil,
        public string $kode,
        public string $pesan,
        public array $data = [],
    ) {}

    public static function sukses(string $kode, string $pesan, array $data = []): self
    {
        return new self(true, $kode, $pesan, $data);
    }

    public static function gagal(string $kode, string $pesan, array $data = []): self
    {
        return new self(false, $kode, $pesan, $data);
    }
}
