<?php

namespace App\Services;

use App\Models\MateriAksara;
use App\Models\PenyelesaianTugas;
use App\Models\RiwayatLatihan;
use App\Models\TugasKelas;
use App\Support\Kelas\HasilOperasi;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Throwable;

/** Verifikasi bukti dari database server, bukan payload browser. */
class PengumpulanTugas
{
    /** Konfirmasi membaca; identitas, materi, dan waktu ditentukan server. */
    public function simpanPenyelesaianMateri(int $idKelas, int $idTugas, int $idKomponen, int $idPelajar): HasilOperasi
    {
        try {
            return DB::transaction(function () use ($idKelas, $idTugas, $idKomponen, $idPelajar): HasilOperasi {
                $kelas = DB::table('kelas_belajar')->where('id_kelas', $idKelas)->lockForUpdate()->first();
                $tugas = TugasKelas::whereKey($idTugas)->where('id_kelas', $idKelas)->where('status', 'terbit')->lockForUpdate()->first();
                $anggota = DB::table('keanggotaan_kelas')->where('id_kelas', $idKelas)->where('id_pelajar', $idPelajar)->lockForUpdate()->first();
                if (! $kelas || strtolower($kelas->status) !== 'aktif' || ! $tugas || ! $anggota || $anggota->status !== 'aktif') {
                    return HasilOperasi::gagal('aksesDitolak', 'Tugas tidak dapat diakses.');
                }
                $komponen = $tugas->komponen->firstWhere('id_komponen', $idKomponen);
                if (! $komponen || $komponen->jenis !== 'materi') {
                    return HasilOperasi::gagal('komponenTidakValid', 'Komponen materi tidak ditemukan.');
                }
                $materi = MateriAksara::with('konten')->whereKey($komponen->id_materi)->where('status_publikasi', 'terbit')->first();
                if (! $materi?->konten || ! $materi->konten->ambilKonten((string) config('ngaksara.versi_aplikasi'))->berhasil()) {
                    return HasilOperasi::gagal('materiTidakTersedia', 'Materi belum dapat dimuat. Penyelesaian belum dicatat.');
                }
                if ($this->progres($tugas, $idPelajar)['selesai'][$idKomponen] !== null) {
                    return HasilOperasi::sukses('materiSudahSelesai', 'Materi sudah dicatat selesai.');
                }
                $hasil = RiwayatLatihan::catatAktivitas([
                    'id_pelajar' => $idPelajar, 'id_tugas' => $idTugas,
                    'id_komponen' => $idKomponen, 'id_materi' => $komponen->id_materi,
                    'mode_aktivitas' => 'materi',
                ]);
                if (! $hasil->berhasil || ! $hasil->ambil('idRiwayat')) {
                    throw new \RuntimeException('Penyelesaian materi tidak tersimpan.');
                }

                return HasilOperasi::sukses('materiSelesai', 'Penyelesaian materi berhasil dicatat.');
            }, 3);
        } catch (Throwable $error) {
            report($error);

            return HasilOperasi::gagal('gagalSimpan', 'Penyelesaian materi gagal disimpan. Silakan coba lagi.');
        }
    }

    /** Helper ambilBuktiPenyelesaian saat ini memakai MAX waktu; finalisasi perlu seluruh atribut bukti dan MIN keberhasilan tiap komponen sebelum MAX antarkomponen. */
    public function progres(TugasKelas $tugas, int $idPelajar): array
    {
        $catatan = RiwayatLatihan::query()->where('id_pelajar', $idPelajar)->where('id_tugas', $tugas->id_tugas)->whereNotNull('waktu_selesai')->where('waktu_selesai', '<=', now())->get();
        $selesai = [];
        $akhir = null;
        $wajib = 0;
        $kurang = [];
        foreach ($tugas->komponen as $c) {
            $awal = null;
            foreach ($catatan as $b) {
                if ((int) $b->id_komponen !== (int) $c->id_komponen || (int) $b->id_materi !== (int) $c->id_materi || $b->mode_aktivitas !== $c->jenis || ($c->jenis === 'latihan' && ($b->mode !== $c->mode || $b->status !== 'berhasil')) || ($c->jenis === 'materi' && $b->status !== 'selesai')) {
                    continue;
                }
                $w = CarbonImmutable::parse($b->waktu_selesai, config('app.timezone'));
                if ($awal === null || $w < $awal) {
                    $awal = $w;
                }
            }
            $selesai[$c->id_komponen] = $awal;
            if ($c->wajib) {
                $wajib++;
                if ($awal === null) {
                    $kurang[] = $c->id_komponen;
                } elseif ($akhir === null || $awal > $akhir) {
                    $akhir = $awal;
                }
            }
        }

        return ['selesai' => $selesai, 'wajib' => $wajib, 'kurang' => $kurang, 'lengkap' => $wajib > 0 && $kurang === [], 'waktu_selesai' => $akhir];
    }

    public function simpanPengumpulan(int $idKelas, int $idTugas, int $idPelajar): HasilOperasi
    {
        try {
            return DB::transaction(function () use ($idKelas, $idTugas, $idPelajar) {
                $kelas = DB::table('kelas_belajar')->where('id_kelas', $idKelas)->lockForUpdate()->first();
                $t = TugasKelas::whereKey($idTugas)->where('id_kelas', $idKelas)->where('status', 'terbit')->lockForUpdate()->first();
                $anggota = DB::table('keanggotaan_kelas')->where('id_kelas', $idKelas)->where('id_pelajar', $idPelajar)->lockForUpdate()->first();
                if (! $kelas || strtolower($kelas->status) !== 'aktif' || ! $t || ! $anggota || $anggota->status !== 'aktif') {
                    return HasilOperasi::gagal('aksesDitolak', 'Tugas tidak dapat diakses.');
                }
                $lama = PenyelesaianTugas::where('id_tugas', $idTugas)->where('id_pelajar', $idPelajar)->lockForUpdate()->first();
                if ($lama) {
                    return HasilOperasi::sukses('sudahDikumpulkan', 'Tugas sudah dikumpulkan.', ['status' => $lama->status]);
                }
                $p = $this->progres($t, $idPelajar);
                if (! $p['lengkap']) {
                    return HasilOperasi::gagal('komponenBelumLengkap', 'Selesaikan seluruh komponen wajib terlebih dahulu.');
                }
                $status = PenyelesaianTugas::tentukanStatusKetepatan($p['waktu_selesai'], $t->tenggat);
                DB::table('penyelesaian_tugas')->insert(['id_tugas' => $idTugas, 'id_pelajar' => $idPelajar, 'waktu_kumpul' => $p['waktu_selesai'], 'status' => $status]);
                $aktivitas = RiwayatLatihan::catatAktivitas([
                    'id_pelajar' => $idPelajar,
                    'id_tugas' => $idTugas,
                    'mode_aktivitas' => 'pengumpulan',
                    'waktu_selesai' => $p['waktu_selesai'],
                ]);
                if (! $aktivitas->berhasil || ! $aktivitas->ambil('idRiwayat')) {
                    throw new \RuntimeException('Aktivitas pengumpulan tidak tersimpan.');
                }

                return HasilOperasi::sukses('tugasDikumpulkan', 'Tugas berhasil dikumpulkan.', ['status' => $status]);
            }, 3);
        } catch (Throwable $e) {
            report($e);

            return HasilOperasi::gagal('gagalSimpan', 'Pengumpulan gagal disimpan. Silakan coba lagi.');
        }
    }
}
