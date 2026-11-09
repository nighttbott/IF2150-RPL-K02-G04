<?php

namespace App\Support\Progres;

/**
 * Tipe "HasilOperasi" pada Tabel 4.1 untuk modul riwayat, motivasi, progres, dan pemantauan.
 * Bentuknya sama dengan App\Support\Materi\HasilOperasi, ditambah pembaca data ambil().
 */
final class HasilOperasi
{
    private function __construct(
        public readonly bool $berhasil,
        public readonly string $kode,
        public readonly string $pesan,
        public readonly array $data = [],
    ) {}

    /** Hasil berhasil beserta data tambahan. */
    public static function sukses(array $data = [], string $pesan = 'Berhasil'): self
    {
        return new self(true, 'OK', $pesan, $data);
    }

    /** Hasil gagal dengan kode galat yang bisa diperiksa pemanggil. */
    public static function gagal(string $kode, string $pesan): self
    {
        return new self(false, $kode, $pesan);
    }

    /** Mengambil satu nilai dari data hasil. */
    public function ambil(string $kunci, mixed $bawaan = null): mixed
    {
        return $this->data[$kunci] ?? $bawaan;
    }
}
