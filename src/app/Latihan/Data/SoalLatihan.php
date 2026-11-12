<?php

namespace App\Latihan\Data;

use App\Enums\ModeLatihan;

/**
 * Soal yang disimpan di session (DPPL 5.16). Templat hanya dipakai server, tidak dikirim ke browser.
 */
final readonly class SoalLatihan
{
    /**
     * @param  list<list<array{x: float, y: float}>>|null  $templat goresan templat aksara
     * @param  list<string>|null  $pilihan
     * @param  list<string>|null  $komponen
     */
    public function __construct(
        public string $idSoal,
        public int $idMateri,
        public ModeLatihan $mode,
        public string $targetSoal,
        public string $latin,
        public ?array $templat = null,
        public ?string $audio = null,
        public ?array $pilihan = null,
        public ?array $komponen = null,
    ) {}

    /** Session memakai serialisasi JSON, jadi soal disimpan sebagai array. */
    public function toArray(): array
    {
        return [...get_object_vars($this), 'mode' => $this->mode->value];
    }

    public static function fromArray(array $data): self
    {
        return new self(...[...$data, 'mode' => ModeLatihan::from($data['mode'])]);
    }
}
