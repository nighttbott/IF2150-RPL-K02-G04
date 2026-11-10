<?php

namespace Database\Seeders;

use App\Models\AkunPengguna;
use App\Models\MateriAksara;
use App\Models\MotivasiBelajar;
use App\Models\RiwayatLatihan;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;

/**
 * Riwayat latihan contoh selama 20 hari terakhir untuk akun Pelajar uji, memakai materi
 * aksara Jawa tingkat 1 dari MateriSeeder. Streak dan poin dihitung dengan aturan aplikasi
 * (MotivasiBelajar::perbaruiStreak dan tambahPoin), bukan diisi manual.
 */
class RiwayatContohSeeder extends Seeder
{
    public function run(): void
    {
        mt_srand(2026); // agar data contoh selalu sama setiap seeding

        $materi = MateriAksara::query()->with('konten')
            ->where('jenis_aksara', 'jawa')->where('tingkat_kesulitan', 1)->where('status_publikasi', 'terbit')
            ->orderBy('id_materi')->get()
            ->filter(fn ($m) => $m->konten !== null)->values();
        if ($materi->isEmpty()) {
            $this->command?->warn('Materi belum ada. Jalankan MateriSeeder lebih dulu.');

            return;
        }

        $mode = ['menulis', 'bunyi', 'merangkai'];
        $kemampuan = ['pelajar@ngaksara.test' => 72, 'budi@ngaksara.test' => 86, 'wulan@ngaksara.test' => 55, 'raka@ngaksara.test' => 65];

        foreach ($kemampuan as $email => $rataRata) {
            $pelajar = AkunPengguna::query()->where('email', $email)->first();
            if (! $pelajar || RiwayatLatihan::query()->where('id_pelajar', $pelajar->id_akun)->exists()) {
                continue; // jangan menggandakan riwayat bila seeder dijalankan ulang
            }
            $motivasi = MotivasiBelajar::milikPelajar($pelajar->id_akun);

            for ($hari = 19; $hari >= 0; $hari--) {
                if (mt_rand(1, 100) <= 25) {
                    continue; // sebagian hari tidak berlatih, sehingga streak bisa terputus
                }
                $tanggal = CarbonImmutable::today()->subDays($hari);
                for ($i = 0; $i < mt_rand(1, 3); $i++) {
                    $m = $materi[mt_rand(0, $materi->count() - 1)];
                    $daftarAksara = preg_split('/\s+/u', trim($m->konten->bentuk_aksara));
                    $soal = $daftarAksara[mt_rand(0, count($daftarAksara) - 1)];
                    $modeSoal = $mode[mt_rand(0, 2)];
                    $skor = max(0, min(100, $rataRata + mt_rand(-30, 25)));
                    $berhasil = $skor >= 70;
                    $waktu = $tanggal->setTime(mt_rand(7, 21), mt_rand(0, 59));
                    if ($waktu->isFuture()) {
                        $waktu = CarbonImmutable::now()->subMinutes(mt_rand(5, 120)); // jangan membuat waktu di masa depan
                    }
                    $pernah = RiwayatLatihan::cekPernahSelesai($pelajar->id_akun, $soal, $modeSoal);

                    $riwayat = RiwayatLatihan::query()->create([
                        'id_pelajar' => $pelajar->id_akun, 'id_materi' => $m->id_materi,
                        'target_soal' => $soal, 'mode' => $modeSoal, 'skor' => $skor,
                        'tanggal_pengerjaan' => $waktu, 'status' => $berhasil ? 'berhasil' : 'perlu_diulang',
                        'mode_aktivitas' => 'latihan', 'waktu_selesai' => $berhasil ? $waktu : null,
                    ]);

                    if ($berhasil) {
                        $motivasi->perbaruiStreak($tanggal);
                        if (! $pernah) {
                            $motivasi->tambahPoin($riwayat->id_riwayat, 10);
                        }
                    }
                }
            }

            if ($motivasi->poin >= 10) {
                $motivasi->berikanBadge('pemula');
            }
        }
    }
}
