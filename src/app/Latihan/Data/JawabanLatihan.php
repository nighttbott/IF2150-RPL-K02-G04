<?php

namespace App\Latihan\Data;

use App\Enums\ModeLatihan;

final readonly class JawabanLatihan
{
    /** @param  list<list<array{x: float, y: float, t?: float}>>|null  $goresan @param  list<string>|null  $urutan */
    public function __construct(
        public string $idSoal,
        public ModeLatihan $mode,
        public ?array $goresan = null,
        public ?string $pilihan = null,
        public ?array $urutan = null,
    ) {}
}
