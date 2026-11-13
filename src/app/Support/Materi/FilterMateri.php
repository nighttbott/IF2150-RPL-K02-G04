<?php

namespace App\Support\Materi;

use App\Enums\JenisAksara;

/** Tipe "FilterMateri" pada Tabel 4.1. */
final class FilterMateri
{
    public function __construct(
        public ?JenisAksara $jenisAksara = null,
        public ?int $tingkatKesulitan = null,
        public bool $termasukDraf = false,
    ) {}

    public static function semuaTermasukDraf(?JenisAksara $jenis = null): self
    {
        return new self($jenis, null, true);
    }

    /** Membaca ?jenis=jawa&tingkat=2 dari query string; nilai tidak dikenal diabaikan. */
    public static function dariQuery(array $query, bool $termasukDraf = false): self
    {
        $jenis = JenisAksara::tryFrom((string) ($query['jenis'] ?? ''));
        $tingkat = filter_var($query['tingkat'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 3]]);

        return new self($jenis, $tingkat === false ? null : $tingkat, $termasukDraf);
    }
}
