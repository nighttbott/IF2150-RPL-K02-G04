<?php

namespace App\Support\Materi;

/** Tipe "PaketMateri" pada Tabel 4.1: isi satu materi siap ditampilkan di I-05. */
final class PaketMateri
{
    /**
     * @param  list<array{karakter: string, kode: ?string}>  $aksara
     */
    public function __construct(
        public ?string $galat = null,
        public int $idMateri = 0,
        public string $namaMateri = '',
        public string $jenisAksara = '',
        public int $tingkatKesulitan = 0,
        public array $aksara = [],
        public string $aturan = '',
        public string $contoh = '',
        public ?string $pathAudio = null,
        public string $versiKonten = '',
        public ?string $statusProgres = null,
    ) {}

    public static function galat(string $kode): self
    {
        return new self(galat: $kode);
    }

    public function berhasil(): bool
    {
        return $this->galat === null;
    }
}
