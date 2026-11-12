<?php

namespace App\Http\Controllers;

use App\Enums\ModeAktivitas;
use App\Enums\Peran;
use App\Enums\StatusKeanggotaan;
use App\Enums\StatusKelas;
use App\Enums\StatusPublikasi;
use App\Enums\StatusRiwayat;
use App\Enums\JenisAksara;
use App\Models\AkunPengguna;
use App\Models\MateriAksara;
use App\Models\MotivasiBelajar;
use App\Models\RiwayatLatihan;
use App\Support\Materi\FilterMateri;
use Carbon\CarbonInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\View\View;

/** C07 HalamanUtama (Untuk I-03).  */
class BerandaController extends Controller
{
    /** Kartu "Kata Pak Aksa"; berganti setiap hari. */
    private const TIPS = [
        'Latihan sedikit tapi rutin lebih manjur daripada sekali banyak.',
        'Ucapkan bunyinya keras-keras saat menulis, supaya lebih cepat hafal.',
        'Aksara yang sering keliru? Ulangi di mode menulis sampai tanganmu ingat.',
        'Sebelum lanjut, coba tulis ulang materi kemarin tanpa melihat contoh.',
    ];

    public function tampilkan(Request $request): View|RedirectResponse
    {
        /** @var AkunPengguna $akun */
        $akun = $request->user();
        $pelajar = $akun->memilikiPeran(Peran::Pelajar);

        // halaman awal adalah menu pertama. Selain Pelajar, menu pertama bukan Beranda.
        if (! $pelajar && ($tujuan = $this->tujuanAwal($akun)) !== null) {
            return redirect()->route($tujuan);
        }

        return view('beranda', [
            'akun'     => $akun,
            'pelajar'  => $pelajar,
            'salam'    => self::salam(now()),
            'motivasi' => $this->ringkasanMotivasi($akun),
        ] + ($pelajar ? $this->ringkasanBelajar((int) $akun->id_akun) : []));
    }

    /** I-03-03: salam mengikuti waktu server. */
    public static function salam(CarbonInterface $waktu): string
    {
        return match (true) {
            $waktu->hour >= 4 && $waktu->hour < 10  => 'Sugeng enjang',
            $waktu->hour >= 10 && $waktu->hour < 15 => 'Sugeng siyang',
            $waktu->hour >= 15 && $waktu->hour < 18 => 'Sugeng sonten',
            default                                  => 'Sugeng dalu',
        };
    }

    /** menu pertama yang route-nya sudah terdaftar. Null bila belum ada satu pun. */
    private function tujuanAwal(AkunPengguna $akun): ?string
    {
        $urutan = $akun->memilikiPeran(Peran::Pengajar)
            ? ['pengajar.kelas.index', 'pemantauan.kelas', 'feedback.index']
            : ['materi.kelola'];

        return collect($urutan)->first(fn (string $nama) => Route::has($nama));
    }

    /** I-03-06. streak yang sudah terputus ditampilkan 0. */
    private function ringkasanMotivasi(AkunPengguna $akun): array
    {
        if (! $akun->memilikiPeran(Peran::Pelajar)) {
            return ['streak' => 0, 'poin' => 0, 'badge' => 0];
        }

        $data = MotivasiBelajar::ambilMotivasi($akun->id_akun);

        return [
            'streak' => $data['streak'],
            'poin'   => $data['poin'],
            'badge'  => count($data['daftarBadge']),
        ];
    }

    /** Data kartu I-03-03 s.d. I-03-07 untuk Pelajar. */
    private function ringkasanBelajar(int $idPelajar): array
    {
        // Algo-101: materi rekomendasi teratas menjadi saran hari ini.
        $rekomendasi = app(ProgresController::class)->siapkanRekomendasi($idPelajar);
        $saran = $rekomendasi[0]['materi'] ?? null;
        $statusProgres = RiwayatLatihan::ambilStatusProgres($idPelajar);

        return [
            'saran'     => $saran,
            'lanjutkan' => $this->materiTerakhir($idPelajar, $statusProgres, $saran),
            'jalur'     => $this->jalurBelajar($saran),
            'target'    => $this->targetHarian($idPelajar),
            'kelas'     => $this->kelasDiikuti($idPelajar),
            'tips'      => self::TIPS[now()->dayOfYear % count(self::TIPS)],
        ];
    }

    /**
     * I-03-04: materi terakhir berstatus sedang (riwayat paling baru).
     * Bila belum ada, kartu menawarkan materi saran dengan progresnya saat ini.
     *
     * @return array{materi: MateriAksara, persen: int}|null
     */
    private function materiTerakhir(int $idPelajar, array $statusProgres, ?MateriAksara $saran): ?array
    {
        $idSedang = array_keys(array_filter($statusProgres, fn (array $p) => $p['status'] === 'sedang'));

        $idTerakhir = $idSedang === [] ? null : RiwayatLatihan::query()
            ->where('id_pelajar', $idPelajar)
            ->whereIn('id_materi', $idSedang)
            ->orderByDesc('tanggal_pengerjaan')
            ->orderByDesc('id_riwayat')
            ->value('id_materi');

        $materi = $idTerakhir
            ? MateriAksara::query()->where('status_publikasi', StatusPublikasi::Terbit->value)->find($idTerakhir)
            : null;
        $materi ??= $saran;

        return $materi ? ['materi' => $materi, 'persen' => $statusProgres[$materi->id_materi]['persen'] ?? 0] : null;
    }

    /**
     * I-03-05: simpul materi pada bagian yang memuat materi saran (Algo-049).
     * Keadaan simpul: selesai, mulai (materi berikutnya), atau terkunci.
     */
    private function jalurBelajar(?MateriAksara $saran): array
    {
        $jenis = $saran?->jenis_aksara?->value ?? 'jawa';
        $tingkat = (int) ($saran?->tingkat_kesulitan ?? 1);

        $daftar = app(MateriController::class)->ambilDaftarMateri(
            FilterMateri::dariQuery(['jenis' => $jenis, 'tingkat' => $tingkat])
        );

        $idBerikutnya = $saran?->id_materi;
        if ($idBerikutnya === null || ! collect($daftar)->contains(fn ($m) => $m->idMateri === $idBerikutnya)) {
            $idBerikutnya = collect($daftar)->first(fn ($m) => $m->statusProgres !== 'selesai')?->idMateri;
        }

        $langkah = array_map(fn ($m) => [
            'materi'  => $m,
            'keadaan' => match (true) {
                $m->statusProgres === 'selesai' => 'selesai',
                $m->idMateri === $idBerikutnya  => 'mulai',
                default                         => 'terkunci',
            },
        ], $daftar);

        return [
            'jenis'   => $jenis,
            'tingkat' => $tingkat,
            'judul'   => JenisAksara::from($jenis)->label(), // bagian = tingkat_kesulitan; judul = jenis aksara
            'langkah' => $langkah,
        ];
    }

    /**
     * I-03-07: latihan berhasil hari ini dibandingkan target harian.
     *
     * @return array{selesai: int, target: int}
     */
    private function targetHarian(int $idPelajar): array
    {
        $selesai = RiwayatLatihan::query()
            ->where('id_pelajar', $idPelajar)
            ->where('mode_aktivitas', ModeAktivitas::Latihan->value)
            ->where('status', StatusRiwayat::Berhasil->value)
            ->whereDate('tanggal_pengerjaan', today())
            ->count();

        return ['selesai' => $selesai, 'target' => max(1, (int) config('ngaksara.target_harian', 3))];
    }

    /**
     * Kartu "Kelasmu": kelas aktif yang diikuti (query sama dengan ProgresController::tampilkan).
     * tugas_menunggu masih null sampai operasi tugas dari modul kelas tersedia.
     */
    private function kelasDiikuti(int $idPelajar): array
    {
        return DB::table('kelas_belajar as k')
            ->join('keanggotaan_kelas as a', 'a.id_kelas', '=', 'k.id_kelas')
            ->where('a.id_pelajar', $idPelajar)
            ->where('a.status', StatusKeanggotaan::Aktif->value)
            ->where('k.status', StatusKelas::Aktif->value)
            ->orderBy('k.nama')
            ->limit(3)
            ->get(['k.id_kelas', 'k.nama'])
            ->map(fn ($k) => ['id_kelas' => (int) $k->id_kelas, 'nama' => $k->nama, 'tugas_menunggu' => null])
            ->all();
    }
}