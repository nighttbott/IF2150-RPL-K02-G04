<?php

namespace App\Support;

use App\Enums\Peran;
use Illuminate\Contracts\Auth\Authenticatable;

/**
 * Pemeriksaan hak akses Pengajar pada modul kelas (DPPL: AkunPengguna.verifikasiHakAkses, C01).
 * Memakai AkunPengguna::verifikasiHakAkses() bila pemilik modul (Ryuza) sudah menambahkannya,
 * sama seperti pola di MateriController; sementara membaca kolom `peran`.
 */
final class HakAkses
{
    private const AKSI_PENGAJAR = [
        'buatKelas', 'buatTugas', 'terbitkanTugas', 'lihatAnggota',
        'kelolaAnggota', 'gantiKode', 'aturScoreboard',
    ];

    public static function izinkan(?Authenticatable $akun, string $aksi): bool
    {
        if ($akun === null) {
            return false;
        }

        if (method_exists($akun, 'verifikasiHakAkses')) {
            return (bool) $akun->verifikasiHakAkses($aksi);
        }

        return in_array($aksi, self::AKSI_PENGAJAR, true)
            && strtolower((string) ($akun->peran ?? '')) === Peran::Pengajar->value;
    }
}
