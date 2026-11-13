<?php

namespace App\Support\Materi;

/** Tipe "TargetLatihan" pada Tabel 4.1, dipakai LatihanController (Naufal/Marvel). */
final class TargetLatihan
{
    /** @param  list<array<string, mixed>>  $butir */
    public function __construct(
        public int $idMateri,
        public string $mode,
        public array $butir = [],
    ) {}

    public static function kosong(int $idMateri = 0, string $mode = ''): self
    {
        return new self($idMateri, $mode, []);
    }

    public function kosongkah(): bool
    {
        return $this->butir === [];
    }
}
