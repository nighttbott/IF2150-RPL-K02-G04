<?php

namespace App\Console\Commands;

use App\Http\Controllers\ProgresController;
use App\Models\RiwayatLatihan;
use App\Support\Periode;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Profiling agregasi progres kelas (bahan laporan B.2.1, pemilik: Natan).
 *
 * Membandingkan dua cara menghitung ringkasan progres kelas (Algo-102):
 *   1. Versi DPPL: membaca riwayat setiap anggota satu per satu (2 query per anggota).
 *   2. Versi dioptimalkan: 3 query agregasi (GROUP BY) berapa pun jumlah anggotanya.
 * Perintah ini mencatat jumlah query dan median waktu, lalu memastikan hasilnya sama.
 *
 *   php artisan ngaksara:profil-progres                 (kelas dengan anggota aktif terbanyak)
 *   php artisan ngaksara:profil-progres 3 --ulang=10 --periode=90
 */
class ProfilProgresKelas extends Command
{
    protected $signature = 'ngaksara:profil-progres
        {idKelas? : ID kelas yang diukur (bawaan: kelas dengan anggota aktif terbanyak)}
        {--ulang=5 : Berapa kali tiap versi dijalankan}
        {--periode=90 : Periode riwayat dalam hari (7, 30, 90, atau semua)}';

    protected $description = 'Mengukur waktu dan jumlah query agregasi progres kelas (versi DPPL vs versi dioptimalkan)';

    public function handle(ProgresController $progres): int
    {
        $idKelas = $this->argument('idKelas') ?? DB::table('keanggotaan_kelas')->where('status', 'aktif')
            ->groupBy('id_kelas')->selectRaw('id_kelas, COUNT(*) AS jumlah')->orderByDesc('jumlah')->value('id_kelas');
        if (! $idKelas) {
            $this->error('Belum ada kelas dengan anggota aktif. Jalankan: php artisan db:seed --class=ProfilingSeeder');

            return self::FAILURE;
        }

        $anggota = array_keys($progres->anggotaAktif((int) $idKelas));
        $periode = Periode::dariPilihan((string) $this->option('periode'));
        $ulang = max(1, (int) $this->option('ulang'));
        $jumlahRiwayat = RiwayatLatihan::query()->whereIn('id_pelajar', $anggota)->count();

        $this->info("Kelas {$idKelas}: ".count($anggota)." anggota aktif, {$jumlahRiwayat} baris riwayat, driver ".DB::getDriverName());

        $naif = $this->ukur(fn () => $this->agregasiVersiDppl($anggota, $periode), $ulang);
        $optimal = $this->ukur(fn () => $progres->agregasiProgres($anggota, $periode), $ulang);

        $this->table(['Versi', 'Jumlah query', 'Median waktu (ms)', 'Waktu tercepat (ms)'], [
            ['DPPL (per anggota)', $naif['query'], $naif['median'], $naif['min']],
            ['Dioptimalkan (GROUP BY)', $optimal['query'], $optimal['median'], $optimal['min']],
        ]);

        // Pastikan kedua versi menghasilkan angka yang sama
        $a = $naif['hasil'];
        $b = $optimal['hasil'];
        $sama = $a['jumlahLatihan'] === $b['jumlahLatihan'] && $a['penguasaanMateri'] == $b['penguasaanMateri'] && $a['perPelajar'] == $b['perPelajar'];
        $sama ? $this->info('Hasil kedua versi SAMA (jumlah latihan, penguasaan per materi, ringkasan per Pelajar).')
              : $this->warn('Hasil kedua versi BERBEDA. Periksa kembali sebelum dilaporkan.');

        if (DB::getDriverName() === 'mysql') {
            $this->line('EXPLAIN query ringkasan per Pelajar (versi dioptimalkan):');
            [$mulai, $selesai] = $periode->rentang();
            $ids = implode(',', array_map('intval', $anggota)) ?: '0';
            $explain = DB::select("EXPLAIN SELECT id_pelajar, COUNT(*), SUM(skor), COUNT(skor), MAX(tanggal_pengerjaan) FROM riwayat_latihan WHERE id_pelajar IN ({$ids}) AND tanggal_pengerjaan BETWEEN ? AND ? GROUP BY id_pelajar", [$mulai, $selesai]);
            $this->table(array_keys((array) $explain[0]), array_map(fn ($r) => (array) $r, $explain));
        }

        return self::SUCCESS;
    }

    /** Menjalankan fungsi beberapa kali, mencatat jumlah query dan waktu tiap putaran. */
    private function ukur(callable $fungsi, int $ulang): array
    {
        $waktu = [];
        $query = 0;
        $hasil = null;
        for ($i = 0; $i < $ulang; $i++) {
            DB::flushQueryLog();
            DB::enableQueryLog();
            $mulai = hrtime(true);
            $hasil = $fungsi();
            $waktu[] = (hrtime(true) - $mulai) / 1e6;
            $query = count(DB::getQueryLog());
            DB::disableQueryLog();
        }
        sort($waktu);

        return ['hasil' => $hasil, 'query' => $query, 'median' => round($waktu[intdiv(count($waktu), 2)], 2), 'min' => round($waktu[0], 2)];
    }

    /**
     * Versi Algo-102 seperti tertulis di DPPL: riwayat dibaca per anggota dengan
     * RiwayatLatihan::ambilRiwayatPelajar (Q-014), lalu dirangkum di PHP.
     */
    private function agregasiVersiDppl(array $anggota, Periode $periode): array
    {
        [$mulai, $selesai] = $periode->rentang();
        $perPelajar = [];
        $perMateri = [];
        $jumlahLatihan = 0;

        foreach ($anggota as $id) {
            $riwayat = RiwayatLatihan::ambilRiwayatPelajar($id)
                ->filter(fn ($r) => $r->tanggal_pengerjaan->format('Y-m-d H:i:s') >= $mulai && $r->tanggal_pengerjaan->format('Y-m-d H:i:s') <= $selesai);
            RiwayatLatihan::hitungPolaKesalahan($id, $periode); // query kedua per anggota, seperti DPPL

            if ($riwayat->isEmpty()) {
                continue;
            }
            $skor = $riwayat->whereNotNull('skor');
            $latihan = $riwayat->where('mode_aktivitas', 'latihan')->count();
            $jumlahLatihan += $latihan;
            $perPelajar[$id] = [
                'jumlahLatihan' => $latihan,
                'rataSkor' => $skor->count() > 0 ? round($skor->sum('skor') / $skor->count(), 2) : null,
                'terakhir' => $riwayat->max('tanggal_pengerjaan')->format('Y-m-d H:i:s'),
            ];
            foreach ($skor as $r) {
                $perMateri[$r->id_materi][] = $r->skor;
            }
        }

        $penguasaan = array_map(fn ($nilai) => round(array_sum($nilai) / count($nilai), 2), $perMateri);
        ksort($penguasaan);
        ksort($perPelajar);

        return ['jumlahLatihan' => $jumlahLatihan, 'penguasaanMateri' => $penguasaan, 'perPelajar' => $perPelajar];
    }
}
