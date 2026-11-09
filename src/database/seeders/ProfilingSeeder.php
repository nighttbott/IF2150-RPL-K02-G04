<?php

namespace Database\Seeders;

use App\Models\AkunPengguna;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Dataset fiktif untuk profiling B.2.1: satu kelas berisi 100 Pelajar aktif
 * dan 10.000 baris riwayat latihan dalam 120 hari terakhir.
 *
 *   php artisan db:seed --class=ProfilingSeeder
 *   php artisan ngaksara:profil-progres
 *
 * Jalankan setelah DatabaseSeeder (butuh materi dan akun Pengajar uji).
 * Data kelas ditulis lewat query langsung agar tidak bergantung pada model modul kelas.
 */
class ProfilingSeeder extends Seeder
{
    public const JUMLAH_PELAJAR = 100;

    public const JUMLAH_RIWAYAT = 10000;

    public function run(): void
    {
        mt_srand(9);
        $pengajar = AkunPengguna::query()->where('email', 'pengajar@ngaksara.test')->firstOrFail();
        $materi = DB::table('materi_aksara')->where('status_publikasi', 'terbit')->pluck('id_materi')->all();
        $target = ['ꦲ', 'ꦤ', 'ꦕ', 'ꦫ', 'ꦏ', 'ꦢ', 'ꦠ', 'ꦱ', 'ꦮ', 'ꦭ', 'ꦥ', 'ꦝ', 'ꦗ', 'ꦪ', 'ꦚ'];
        $mode = ['menulis', 'bunyi', 'merangkai'];

        $idKelas = DB::table('kelas_belajar')->where('kode_bergabung', 'PRF100')->value('id_kelas')
            ?? DB::table('kelas_belajar')->insertGetId([
                'nama' => 'Kelas Profiling 100 Pelajar', 'pemilik_id' => $pengajar->id_akun,
                'kode_bergabung' => 'PRF100', 'status' => 'aktif', 'status_scoreboard' => false,
            ], 'id_kelas');

        // Pelajar fiktif dibuat dengan insert massal agar cepat
        $hash = Hash::make('Rahasia123!');
        $baris = [];
        for ($i = 1; $i <= self::JUMLAH_PELAJAR; $i++) {
            $baris[] = ['email' => "profil{$i}@ngaksara.test", 'username' => "profil{$i}", 'password_hash' => $hash,
                'peran' => 'pelajar', 'nama_tampilan' => "Pelajar {$i}", 'created_at' => now(), 'updated_at' => now()];
        }
        AkunPengguna::query()->insertOrIgnore($baris);
        $idPelajar = AkunPengguna::query()->where('email', 'like', 'profil%@ngaksara.test')->pluck('id_akun')->all();

        DB::table('keanggotaan_kelas')->insertOrIgnore(array_map(fn ($id) => ['id_kelas' => $idKelas, 'id_pelajar' => $id, 'status' => 'aktif'], $idPelajar));
        DB::table('motivasi_belajar')->insertOrIgnore(array_map(fn ($id) => ['id_pelajar' => $id, 'streak' => 0, 'poin' => mt_rand(0, 400)], $idPelajar));

        // Jangan menggandakan riwayat bila seeder dijalankan ulang
        if (DB::table('riwayat_latihan')->whereIn('id_pelajar', $idPelajar)->exists()) {
            $this->command?->info('Riwayat profiling sudah ada, dilewati.');

            return;
        }

        $hariIni = CarbonImmutable::today();
        $riwayat = [];
        for ($i = 0; $i < self::JUMLAH_RIWAYAT; $i++) {
            $skor = mt_rand(20, 100);
            $waktu = $hariIni->subDays(mt_rand(1, 119))->setTime(mt_rand(6, 22), mt_rand(0, 59))->format('Y-m-d H:i:s'); // mulai kemarin agar tidak ada waktu di masa depan
            $riwayat[] = [
                'id_pelajar' => $idPelajar[array_rand($idPelajar)], 'id_materi' => $materi[array_rand($materi)],
                'target_soal' => $target[array_rand($target)], 'mode' => $mode[array_rand($mode)], 'skor' => $skor,
                'tanggal_pengerjaan' => $waktu, 'status' => $skor >= 70 ? 'berhasil' : 'perlu_diulang',
                'mode_aktivitas' => 'latihan', 'waktu_selesai' => $skor >= 70 ? $waktu : null,
                'id_tugas' => null, 'id_komponen' => null,
            ];
            if (count($riwayat) === 1000) {
                DB::table('riwayat_latihan')->insert($riwayat);
                $riwayat = [];
            }
        }
        if ($riwayat) {
            DB::table('riwayat_latihan')->insert($riwayat);
        }

        $this->command?->info('Dataset profiling siap: kelas id '.$idKelas.', '.count($idPelajar).' Pelajar, '.self::JUMLAH_RIWAYAT.' riwayat.');
    }
}
