<?php

namespace App\Support\Materi;

use App\Enums\JenisAksara;
use App\Enums\StatusPublikasi;

/** Tipe "MetadataMateri" pada Tabel 4.1. statusProgres/persen hanya terisi untuk Pelajar. */
final class MetadataMateri
{
    public function __construct(
        public ?int $idMateri,
        public string $namaMateri,
        public JenisAksara $jenisAksara,
        public int $tingkatKesulitan,
        public StatusPublikasi $statusPublikasi,
        public ?string $statusProgres = null,
        public ?int $persenProgres = null,
        public ?string $versiKonten = null,
    ) {}
}
