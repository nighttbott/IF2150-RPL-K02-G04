<?php

namespace App\Http\Controllers;

use App\Enums\ModeAktivitas;
use App\Enums\StatusKeanggotaan;
use App\Enums\StatusKelas;
use App\Enums\StatusRiwayat;
use App\Models\AkunPengguna;
use App\Models\MateriAksara;
use App\Models\MotivasiBelajar;
use App\Models\RiwayatLatihan;
use App\Support\Periode;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * C19 ProgresController (pemilik modul: Natan).
 *
 * Mengolah riwayat dan progres belajar, menyiapkan rekomendasi materi, indikator
 * motivasi, dan papan peringkat (DPPL2 subbab 5.19, Algo-100 sampai Algo-102).
 * Aksi halaman: tampilkan() untuk I-11 Progres Pelajar.
 * Operasi lain bersifat publik karena juga dipakai PemantauanKelasController (UC-08).
 * Data kelas dibaca lewat query langsung agar modul ini tidak bergantung pada model modul kelas.
 */
class ProgresController extends Controller
{
    /** Pilihan periode pada halaman progres. */
    public const PILIHAN_PERIODE = ['7' => '7 hari terakhir', '30' => '30 hari terakhir', '90' => '90 hari terakhir', 'semua' => 'Semua riwayat'];

    /**
     * Halaman I-11 (bukaHalamanProgres, pilihPeriode, pilihMateriRiwayat, bukaPapanPeringkat).
     */
    public function tampilkan(Request $request): View
    {
        $idPelajar = (int) $request->user()->id_akun;

        $pilihanPeriode = array_key_exists((string) $request->query('periode'), self::PILIHAN_PERIODE) ? (string) $request->query('periode') : '30';
        $periode = Periode::dariPilihan($pilihanPeriode);

        $ringkasan = $this->hitungProgres($idPelajar, $periode);
        $riwayat = $this->ambilRiwayat($idPelajar, $periode);
        $rekomendasi = $this->siapkanRekomendasi($idPelajar);
        $motivasi = $this->ambilIndikatorMotivasi($idPelajar);

        // Detail hasil satu materi (pilihMateriRiwayat)
        $idMateri = $request->integer('materi') ?: null;
        $detail = $idMateri ? $this->ambilDetailHasil($idPelajar, $idMateri) : null;
        $materiDetail = $idMateri ? MateriAksara::query()->find($idMateri) : null;

        // Kelas aktif yang diikuti Pelajar, untuk memilih papan peringkat
        $kelasDiikuti = DB::table('kelas_belajar as k')
            ->join('keanggotaan_kelas as a', 'a.id_kelas', '=', 'k.id_kelas')
            ->where('a.id_pelajar', $idPelajar)
            ->where('a.status', StatusKeanggotaan::Aktif->value)
            ->where('k.status', StatusKelas::Aktif->value)
            ->orderBy('k.nama')
            ->get(['k.id_kelas', 'k.nama', 'k.status_scoreboard']);
        $idKelas = $request->integer('kelas') ?: $kelasDiikuti->first()?->id_kelas;
        $scoreboard = $idKelas ? $this->ambilScoreboard($idKelas) : null;

        return view('progres.index', [
            'pilihanPeriode' => $pilihanPeriode,
            'ringkasan' => $ringkasan,
            'riwayat' => $riwayat,
            'rekomendasi' => $rekomendasi,
            'motivasi' => $motivasi,
            'detail' => $detail,
            'materiDetail' => $materiDetail,
            'kelasDiikuti' => $kelasDiikuti,
            'idKelasTerpilih' => $idKelas,
            'scoreboard' => $scoreboard,
            'idPelajar' => $idPelajar,
        ]);
    }

    /**
     * ambilRiwayat(idPelajar, periode): List<RiwayatLatihan>  (Q-011)
     */
    public function ambilRiwayat(int $idPelajar, Periode $periode): Collection
    {
        return RiwayatLatihan::ambilRiwayat($idPelajar, $periode);
    }

    /**
     * hitungProgres(idPelajar): RingkasanProgres  (Algo-100)
     *
     * Penguasaan per materi, jumlah latihan, dan pola kesalahan. Penguasaan dihitung
     * dari riwayat setiap kali dibutuhkan (tidak disimpan). Periode opsional; bila
     * kosong seluruh riwayat dipakai.
     */
    public function hitungProgres(int $idPelajar, ?Periode $periode = null): array
    {
        $penguasaan = RiwayatLatihan::hitungPenguasaan($idPelajar, $periode);

        $materi = MateriAksara::query()
            ->whereIn('id_materi', array_keys($penguasaan))
            ->get(['id_materi', 'nama_materi', 'jenis_aksara'])
            ->keyBy('id_materi');

        $daftar = collect($penguasaan)
            ->map(fn (float $nilai, int $id) => [
                'id_materi' => $id,
                'nama_materi' => $materi->get($id)?->nama_materi ?? 'Materi #'.$id,
                'jenis_aksara' => $materi->get($id)?->jenis_aksara?->value,
                'nilai' => $nilai,
            ])
            ->sortBy('nama_materi')
            ->values()
            ->all();

        $jumlahLatihan = RiwayatLatihan::query()
            ->where('id_pelajar', $idPelajar)
            ->where('mode_aktivitas', ModeAktivitas::Latihan->value)
            ->when($periode, fn ($q) => $q->whereBetween('tanggal_pengerjaan', $periode->rentang()))
            ->count();

        return [
            'penguasaan' => $daftar,
            'jumlahLatihan' => $jumlahLatihan,
            'polaKesalahan' => $this->hitungPolaKesalahan($idPelajar, $periode),
            'kosong' => $jumlahLatihan === 0,
        ];
    }

    /**
     * siapkanRekomendasi(idPelajar): List<MetadataMateri>  (Algo-101)
     *
     * Memakai MateriAksara::ambilMateriRekomendasi (modul materi, Diandra): materi terbit yang
     * belum tuntas, dengan tingkat berikutnya terbuka bila tingkat di bawahnya sudah tuntas.
     * Bila belum ada riwayat, hasilnya materi tingkat paling awal (UC-04 alternatif 1).
     *
     * @return list<array{materi: MateriAksara, penguasaan: float|null}>
     */
    public function siapkanRekomendasi(int $idPelajar): array
    {
        $penguasaan = RiwayatLatihan::hitungPenguasaan($idPelajar);

        return array_map(
            fn (MateriAksara $m) => ['materi' => $m, 'penguasaan' => $penguasaan[$m->id_materi] ?? null],
            MateriAksara::ambilMateriRekomendasi($penguasaan),
        );
    }

    /**
     * ambilIndikatorMotivasi(idPelajar): Data
     * Streak, poin, dan badge (beserta nama tampilan badge) dari MotivasiBelajar.
     */
    public function ambilIndikatorMotivasi(int $idPelajar): array
    {
        $motivasi = MotivasiBelajar::ambilMotivasi($idPelajar);
        $motivasi['badge'] = collect(MotivasiBelajar::BADGE)
            ->map(fn ($nama, $kode) => ['kode' => $kode, 'nama' => $nama, 'dimiliki' => in_array($kode, $motivasi['daftarBadge'], true)])
            ->values()->all();

        return $motivasi;
    }

    /**
     * ambilScoreboard(idKelas): HasilScoreboard  (memakai Algo-031)
     *
     * Papan peringkat hanya untuk anggota aktif kelas (atau Pengajar pemilik kelas)
     * dan hanya bila Pengajar mengaktifkannya (UC-04 alternatif 2).
     *
     * @return array{aktif: bool, pesan: string|null, daftar: array}
     */
    public function ambilScoreboard(int $idKelas): array
    {
        $kelas = DB::table('kelas_belajar')->where('id_kelas', $idKelas)->where('status', StatusKelas::Aktif->value)->first();
        if (! $kelas) {
            return ['aktif' => false, 'pesan' => 'Kelas tidak ditemukan.', 'daftar' => []];
        }

        $idAkun = (int) auth()->id();
        $anggota = $this->anggotaAktif($idKelas);
        if (! array_key_exists($idAkun, $anggota) && (int) $kelas->pemilik_id !== $idAkun) {
            return ['aktif' => false, 'pesan' => 'Anda bukan anggota aktif kelas ini.', 'daftar' => []];
        }

        if (! $kelas->status_scoreboard) {
            return ['aktif' => false, 'pesan' => 'Papan peringkat belum diaktifkan oleh Pengajar.', 'daftar' => []];
        }

        return ['aktif' => true, 'pesan' => null, 'daftar' => MotivasiBelajar::susunScoreboard($anggota)];
    }

    /**
     * agregasiProgres(anggotaAktif, periode): RingkasanProgres  (Algo-102, versi dioptimalkan)
     *
     * DPPL menulis versi yang membaca riwayat per anggota (2 query per anggota).
     * Versi ini memakai 3 query agregasi tetap berapa pun jumlah anggotanya
     * (hasilnya sama; dibuktikan oleh test dan perintah ngaksara:profil-progres).
     *
     * @param  array<int, int>  $anggotaAktif  daftar id Pelajar berstatus aktif
     */
    public function agregasiProgres(array $anggotaAktif, Periode $periode): array
    {
        $kosong = ['jumlahAnggota' => count($anggotaAktif), 'jumlahLatihan' => 0, 'rataPenguasaan' => null,
            'perPelajar' => [], 'penguasaanMateri' => [], 'polaKesalahan' => [], 'kosong' => true];
        if ($anggotaAktif === []) {
            return $kosong;
        }

        $dasar = fn () => RiwayatLatihan::query()
            ->whereIn('id_pelajar', $anggotaAktif)
            ->whereBetween('tanggal_pengerjaan', $periode->rentang());

        // Query 1: ringkasan per Pelajar
        $perPelajar = $dasar()
            ->groupBy('id_pelajar')
            ->selectRaw("id_pelajar,
                SUM(CASE WHEN mode_aktivitas = 'latihan' THEN 1 ELSE 0 END) AS jumlah_latihan,
                SUM(skor) AS total_skor, COUNT(skor) AS jumlah_skor,
                MAX(tanggal_pengerjaan) AS terakhir")
            ->get()
            ->mapWithKeys(fn ($b) => [(int) $b->id_pelajar => [
                'jumlahLatihan' => (int) $b->jumlah_latihan,
                'rataSkor' => (int) $b->jumlah_skor > 0 ? round((float) $b->total_skor / (int) $b->jumlah_skor, 2) : null,
                'terakhir' => $b->terakhir,
            ]])->all();

        // Query 2: penguasaan per materi (gabungan seluruh anggota)
        $perMateri = $dasar()
            ->whereNotNull('skor')
            ->whereNotNull('id_materi')
            ->groupBy('id_materi')
            ->selectRaw('id_materi, SUM(skor) AS total_skor, COUNT(skor) AS jumlah_skor')
            ->get();
        $penguasaanMateri = $perMateri->mapWithKeys(fn ($b) => [(int) $b->id_materi => round((float) $b->total_skor / (int) $b->jumlah_skor, 2)])->all();
        $totalSkor = (float) $perMateri->sum('total_skor');
        $jumlahSkor = (int) $perMateri->sum('jumlah_skor');

        // Query 3: lima aksara yang paling sering salah
        $pola = $dasar()
            ->where('status', StatusRiwayat::PerluDiulang->value)
            ->whereNotNull('target_soal')
            ->groupBy('target_soal')
            ->selectRaw('target_soal, COUNT(*) AS jumlah_salah')
            ->orderByDesc('jumlah_salah')->orderBy('target_soal')
            ->limit(5)
            ->pluck('jumlah_salah', 'target_soal')
            ->map(fn ($n) => (int) $n)->all();

        $jumlahLatihan = array_sum(array_column($perPelajar, 'jumlahLatihan'));
        ksort($penguasaanMateri);

        return [
            'jumlahAnggota' => count($anggotaAktif),
            'jumlahLatihan' => $jumlahLatihan,
            'rataPenguasaan' => $jumlahSkor > 0 ? round($totalSkor / $jumlahSkor, 2) : null,
            'perPelajar' => $perPelajar,
            'penguasaanMateri' => $penguasaanMateri,
            'polaKesalahan' => $pola,
            'kosong' => $perPelajar === [],
        ];
    }

    /**
     * hitungPolaKesalahan(idPelajar): Data  (Q-013)
     */
    public function hitungPolaKesalahan(int $idPelajar, ?Periode $periode = null): array
    {
        return RiwayatLatihan::hitungPolaKesalahan($idPelajar, $periode);
    }

    /**
     * ambilDetailHasil(idPelajar, idMateri): List<RiwayatLatihan>
     */
    public function ambilDetailHasil(int $idPelajar, int $idMateri): Collection
    {
        return RiwayatLatihan::ambilRiwayatMateri($idPelajar, $idMateri);
    }

    /**
     * Peta id_pelajar => nama_tampilan untuk anggota aktif sebuah kelas.
     * (Pembantu internal; setara KeanggotaanKelas::daftarAnggotaAktif milik modul Abduh.)
     *
     * @return array<int, string>
     */
    public function anggotaAktif(int $idKelas): array
    {
        return AkunPengguna::query()
            ->join('keanggotaan_kelas as k', 'k.id_pelajar', '=', 'akun_pengguna.id_akun')
            ->where('k.id_kelas', $idKelas)
            ->where('k.status', StatusKeanggotaan::Aktif->value)
            ->orderBy('akun_pengguna.nama_tampilan')
            ->pluck('akun_pengguna.nama_tampilan', 'akun_pengguna.id_akun')
            ->mapWithKeys(fn ($nama, $id) => [(int) $id => $nama])
            ->all();
    }
}
