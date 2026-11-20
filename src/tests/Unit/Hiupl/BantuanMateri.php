<?php

namespace Tests\Unit\Hiupl;

use App\Enums\JenisAksara;
use App\Enums\StatusPublikasi;
use App\Models\KontenMateri;
use App\Models\MateriAksara;
use App\Support\Materi\DataKonten;

/** Pembuat data materi lengkap (metadata + konten) untuk pengujian HIUPL. */
trait BantuanMateri
{
    protected function dataKonten(string $nama = 'Nglegena 1', int $tingkat = 1, StatusPublikasi $status = StatusPublikasi::Terbit, ?string $audio = null): DataKonten
    {
        return new DataKonten($nama, JenisAksara::Jawa, $tingkat, $status, 'ꦲ ꦤ', 'Tulis dari kiri ke kanan.', 'ꦲꦤ = "ana"', $audio);
    }

    protected function materiLengkap(string $nama = 'Nglegena 1', int $tingkat = 1, StatusPublikasi $status = StatusPublikasi::Terbit, ?string $audio = null): MateriAksara
    {
        $data = $this->dataKonten($nama, $tingkat, $status, $audio);
        $materi = new MateriAksara;
        $materi->simpan($data->metadata());
        (new KontenMateri(['id_materi' => $materi->id_materi]))->simpanKonten($data);

        return $materi->fresh();
    }
}
