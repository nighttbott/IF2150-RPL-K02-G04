<?php

namespace Tests\Feature;

use App\Models\AkunPengguna;
use App\Models\MateriAksara;
use App\Models\RiwayatLatihan;
use Illuminate\Support\Facades\DB;

/**
 * Fungsi pembantu untuk menyiapkan data uji modul riwayat, progres, dan pemantauan.
 * Data kelas ditulis lewat query langsung agar tidak bergantung pada model modul kelas.
 */
trait BantuanData
{
    protected function buatPelajar(string $nama = 'Sari'): AkunPengguna
    {
        return AkunPengguna::factory()->create(['nama_tampilan' => $nama]);
    }

    protected function buatPengajar(string $nama = 'Bu Rina'): AkunPengguna
    {
        return AkunPengguna::factory()->pengajar()->create(['nama_tampilan' => $nama]);
    }

    protected function buatMateri(string $nama = 'Nglegena 1', string $status = 'terbit', int $tingkat = 1, string $jenis = 'jawa'): MateriAksara
    {
        return MateriAksara::query()->create([
            'nama_materi' => $nama, 'jenis_aksara' => $jenis, 'tingkat_kesulitan' => $tingkat, 'status_publikasi' => $status,
        ]);
    }

    /** Membuat kelas aktif dan mengembalikan id_kelas. */
    protected function buatKelas(AkunPengguna $pengajar, bool $scoreboard = true, string $nama = 'Kelas Uji'): int
    {
        return DB::table('kelas_belajar')->insertGetId([
            'nama' => $nama, 'pemilik_id' => $pengajar->id_akun,
            'kode_bergabung' => strtoupper(substr(md5(uniqid('', true)), 0, 6)),
            'status' => 'aktif', 'status_scoreboard' => $scoreboard,
        ], 'id_kelas');
    }

    protected function gabung(int $idKelas, AkunPengguna $pelajar, string $status = 'aktif'): void
    {
        DB::table('keanggotaan_kelas')->insert(['id_kelas' => $idKelas, 'id_pelajar' => $pelajar->id_akun, 'status' => $status]);
    }

    protected function riwayat(AkunPengguna $pelajar, MateriAksara $materi, float $skor, string $status = 'berhasil', ?string $tanggal = null, string $target = 'ꦲ', string $mode = 'menulis'): RiwayatLatihan
    {
        $waktu = $tanggal ? now()->parse($tanggal) : now();

        return RiwayatLatihan::query()->create([
            'id_pelajar' => $pelajar->id_akun, 'id_materi' => $materi->id_materi, 'target_soal' => $target, 'mode' => $mode,
            'skor' => $skor, 'tanggal_pengerjaan' => $waktu, 'status' => $status, 'mode_aktivitas' => 'latihan',
            'waktu_selesai' => $status === 'berhasil' ? $waktu : null,
        ]);
    }
}
