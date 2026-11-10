<?php

namespace App\Latihan\Data;

final readonly class HasilPenilaian
{
    /**
     * @param  list<int>  $posisiSalah
     */
    public function __construct(
        public float $skor,
        public string $status,
        public string $umpanBalik,
        public array $posisiSalah = [],
    ) {}
}
