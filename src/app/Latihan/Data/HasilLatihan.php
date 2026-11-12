<?php

namespace App\Latihan\Data;

use App\Enums\ModeLatihan;

final readonly class HasilLatihan
{
    public function __construct(
        public int $idPelajar,
        public int $idMateri,
        public string $idSoal,
        public string $targetSoal,
        public ModeLatihan $mode,
        public float $skor,
        public string $status,
    ) {}

    /** Session memakai serialisasi JSON, jadi hasil disimpan sebagai array. */
    public function toArray(): array
    {
        return [...get_object_vars($this), 'mode' => $this->mode->value];
    }

    public static function fromArray(array $data): self
    {
        return new self(...[...$data, 'mode' => ModeLatihan::from($data['mode'])]);
    }
}
