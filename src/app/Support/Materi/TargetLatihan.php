<?php

namespace App\Support\Materi;

/**
 * Tipe "TargetLatihan" pada Tabel 4.1, dipakai LatihanController (Naufal/Marvel).
 * Bentuk satu butir:
 *  - menulis/bunyi : ['karakter' => 'ꦲ', 'bunyi' => 'ha'|null, 'template' => list<list<{x,y}>>|null]
 *  - merangkai     : ['kata' => 'ꦲꦤ', 'bunyi' => 'ana']
 */
final class TargetLatihan
{
    /**
     * @param  list<array<string, mixed>>  $butir
     */
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
