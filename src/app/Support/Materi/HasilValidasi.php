<?php

namespace App\Support\Materi;

/** Tipe "HasilValidasi" pada Tabel 4.1. */
final class HasilValidasi
{
    private function __construct(public readonly bool $valid, public readonly string $pesan = '') {}

    public static function valid(): self
    {
        return new self(true);
    }

    public static function tidakValid(string $pesan): self
    {
        return new self(false, $pesan);
    }
}
