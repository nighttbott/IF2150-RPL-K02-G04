<?php

namespace App\Services\Latihan;

/** Hasil penilaian satu soal. Skor 0 sampai 100, dihitung di server. */
final class HasilPenilaian
{
    /**
     * @param  list<int>  $posisiSalah  indeks (mulai 0) posisi yang salah, khusus mode merangkai
     */
    public function __construct(
        public readonly float $skor,
        public readonly bool $benar,
        public readonly array $posisiSalah = [],
    ) {}

    public function berhasil(float $ambang): bool
    {
        return $this->skor >= $ambang;
    }
}
