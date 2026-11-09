<?php

namespace App\Support\Materi;

use App\Enums\JenisAksara;
use App\Enums\StatusPublikasi;
use Illuminate\Http\UploadedFile;

/** Tipe "DataKonten" pada Tabel 4.1: metadata materi + isi konten dari form I-19. */
final class DataKonten
{
    public function __construct(
        public string $namaMateri,
        public JenisAksara $jenisAksara,
        public int $tingkatKesulitan,
        public StatusPublikasi $statusPublikasi = StatusPublikasi::Draf,
        public string $bentukAksara = '',
        public string $aturan = '',
        public string $contoh = '',
        public ?string $pathAudio = null,
        public string $versiAplikasiMinimum = '1.0',
        public ?UploadedFile $berkasBaru = null,
    ) {}

    public function metadata(?int $idMateri = null): MetadataMateri
    {
        return new MetadataMateri($idMateri, $this->namaMateri, $this->jenisAksara, $this->tingkatKesulitan, $this->statusPublikasi);
    }

    public function kontenLengkap(): bool
    {
        return trim($this->bentukAksara) !== '' && trim($this->aturan) !== '' && trim($this->contoh) !== '';
    }
}
