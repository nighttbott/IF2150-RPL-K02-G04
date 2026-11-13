<?php

namespace App\Latihan\Data;

final readonly class RingkasanMotivasi
{
    /** @param  list<string>  $badgeBaru */
    public function __construct(
        public int $streak,
        public int $poin,
        public bool $poinBertambah,
        public array $badgeBaru,
    ) {}

    /** Session memakai serialisasi JSON, jadi ringkasan disimpan sebagai array. */
    public function toArray(): array
    {
        return get_object_vars($this);
    }

    public static function fromArray(array $data): self
    {
        return new self(...$data);
    }
}
