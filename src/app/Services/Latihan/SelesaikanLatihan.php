<?php

namespace App\Services\Latihan;

use App\Models\MotivasiBelajar;
use App\Models\RiwayatLatihan;
use App\Support\Progres\HasilOperasi;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;

/**
 * Algo-022 selesaikanLatihan untuk satu sesi soal: dalam satu DB::transaction simpan hasil tiap
 * soal, perbarui streak, tambah poin hanya untuk soal yang baru pertama kali berhasil, dan beri badge.
 * Hasil perlu_diulang tidak mengubah streak dan poin.
 *
 * Dibuat Marvel agar mode non-kanvas bisa menyimpan hasil tanpa menunggu LatihanController (Naufal).
 * Bila Naufal sudah punya operasi setara, controller cukup memanggil operasi itu.
 */
final class SelesaikanLatihan
{
    /**
     * @param  list<array<string, mixed>>  $daftarHasil  tiap elemen berisi kunci simpanHasil
     *                                                   (id_materi, target_soal, mode, skor, status, opsional id_tugas dan id_komponen)
     *                                                   tanpa id_pelajar
     * @return HasilOperasi data: idRiwayat (list), poinDidapat, totalPoin, streak, badgeBaru (list kode)
     */
    public function selesaikan(int $idPelajar, array $daftarHasil): HasilOperasi
    {
        if ($daftarHasil === []) {
            return HasilOperasi::gagal('TANPA_HASIL', 'Tidak ada hasil untuk disimpan.');
        }

        try {
            return DB::transaction(function () use ($idPelajar, $daftarHasil) {
                $motivasi = MotivasiBelajar::milikPelajar($idPelajar, kunci: true);

                $idRiwayat = [];
                $barisBaru = [];
                $adaBerhasil = false;
                foreach ($daftarHasil as $hasil) {
                    $berhasil = $hasil['status'] === 'berhasil';
                    $pernah = $berhasil && RiwayatLatihan::cekPernahSelesai($idPelajar, $hasil['target_soal'], $hasil['mode']);

                    $simpan = RiwayatLatihan::simpanHasil($hasil + ['id_pelajar' => $idPelajar]);
                    if (! $simpan->berhasil) {
                        throw new RuntimeException($simpan->pesan);
                    }
                    $id = (int) $simpan->ambil('idRiwayat');
                    $idRiwayat[] = $id;
                    $adaBerhasil = $adaBerhasil || $berhasil;
                    if ($berhasil && ! $pernah) {
                        $barisBaru[] = $id;
                    }
                }

                $poinDidapat = 0;
                $badgeBaru = [];
                if ($adaBerhasil) {
                    $motivasi->perbaruiStreak(today());

                    $nilai = (int) config('ngaksara.latihan.poin_per_soal', 10);
                    foreach ($barisBaru as $id) {
                        $tambah = $motivasi->tambahPoin($id, $nilai);
                        if (! $tambah->berhasil) {
                            throw new RuntimeException($tambah->pesan);
                        }
                        $poinDidapat += $nilai;
                    }

                    $syarat = [
                        'pemula' => $motivasi->poin >= 10,
                        'konsisten_7' => $motivasi->streak >= 7,
                        'seratus_poin' => $motivasi->poin >= 100,
                    ];
                    foreach ($syarat as $kode => $terpenuhi) {
                        if ($terpenuhi && $motivasi->berikanBadge($kode)->ambil('baru')) {
                            $badgeBaru[] = $kode;
                        }
                    }
                }

                return HasilOperasi::sukses([
                    'idRiwayat' => $idRiwayat,
                    'poinDidapat' => $poinDidapat,
                    'totalPoin' => $motivasi->poin,
                    'streak' => $motivasi->streak,
                    'badgeBaru' => $badgeBaru,
                ]);
            });
        } catch (Throwable $e) {
            report($e);

            return HasilOperasi::gagal('SIMPAN_GAGAL', 'Hasil latihan gagal disimpan. Silakan coba lagi.');
        }
    }
}
