<?php

namespace App\Models;

use App\Enums\ModeAktivitas;
use App\Enums\ModeLatihan;
use App\Enums\StatusRiwayat;
use App\Support\Progres\HasilOperasi;
use App\Support\Periode;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * C03 RiwayatLatihan (pemilik modul: Natan).
 *
 * Sumber data tunggal untuk hasil latihan, aktivitas belajar, dan bukti penyelesaian
 * komponen tugas. Modul lain (latihan, materi, tugas, progres, pemantauan) sebaiknya membaca
 * dan menulis riwayat melalui operasi pada kelas ini.
 *
 * Penamaan operasi mengikuti DPPL2 subbab 5.3. Query mengikuti Q-008 sampai Q-016,
 * ditulis dengan query builder agar berjalan di MySQL maupun SQLite (pengujian).
 * Semua nilai mode dan status memakai huruf kecil (lihat App\Enums).
 */
class RiwayatLatihan extends Model
{
    protected $table = 'riwayat_latihan';

    protected $primaryKey = 'id_riwayat';

    public $timestamps = false;

    protected $fillable = [
        'id_pelajar', 'id_materi', 'target_soal', 'mode', 'skor', 'tanggal_pengerjaan',
        'status', 'id_tugas', 'id_komponen', 'mode_aktivitas', 'waktu_selesai',
    ];

    protected function casts(): array
    {
        return [
            'skor' => 'float',
            'tanggal_pengerjaan' => 'datetime',
            'waktu_selesai' => 'datetime',
        ];
    }

    public function materi(): BelongsTo
    {
        return $this->belongsTo(MateriAksara::class, 'id_materi', 'id_materi');
    }

    public function pelajar(): BelongsTo
    {
        return $this->belongsTo(AkunPengguna::class, 'id_pelajar', 'id_akun');
    }

    // ------------------------------------------------------------------
    // Operasi penulisan
    // ------------------------------------------------------------------

    /**
     * simpanHasil(hasil: HasilLatihan): HasilOperasi  (DPPL 5.3.1, Q-008)
     *
     * Menyimpan hasil satu latihan. Kunci yang wajib ada pada $hasil:
     *   id_pelajar, id_materi, target_soal, mode, skor, status ('berhasil' atau 'perlu_diulang').
     * Kunci tambahan (opsional) bila latihan berasal dari tugas kelas:
     *   id_tugas, id_komponen. Keduanya diperlukan agar latihan tercatat sebagai bukti
     *   penyelesaian tugas (perubahan terhadap Q-008, dicatat di B.1).
     *
     * Operasi ini tidak memberi poin atau streak; itu tugas LatihanController (Algo-026).
     */
    public static function simpanHasil(array $hasil): HasilOperasi
    {
        $wajib = ['id_pelajar', 'id_materi', 'target_soal', 'mode', 'skor', 'status'];
        foreach ($wajib as $kunci) {
            if (! array_key_exists($kunci, $hasil)) {
                return HasilOperasi::gagal('DATA_TIDAK_LENGKAP', "Kolom {$kunci} wajib diisi");
            }
        }

        $status = $hasil['status'] instanceof StatusRiwayat ? $hasil['status']->value : (string) $hasil['status'];
        if (! in_array($status, [StatusRiwayat::Berhasil->value, StatusRiwayat::PerluDiulang->value], true)) {
            return HasilOperasi::gagal('STATUS_TIDAK_VALID', 'Status latihan harus berhasil atau perlu_diulang');
        }

        $mode = $hasil['mode'] instanceof ModeLatihan ? $hasil['mode']->value : strtolower((string) $hasil['mode']);
        if (! in_array($mode, ModeLatihan::nilai(), true)) {
            return HasilOperasi::gagal('MODE_TIDAK_VALID', 'Mode latihan tidak dikenal');
        }

        $skor = (float) $hasil['skor'];
        if ($skor < 0 || $skor > 100) {
            return HasilOperasi::gagal('SKOR_TIDAK_VALID', 'Skor harus di antara 0 dan 100');
        }

        $sekarang = now();
        $riwayat = self::create([
            'id_pelajar' => $hasil['id_pelajar'],
            'id_materi' => $hasil['id_materi'],
            'target_soal' => $hasil['target_soal'],
            'mode' => $mode,
            'skor' => round($skor, 2),
            'tanggal_pengerjaan' => $sekarang,
            'status' => $status,
            'id_tugas' => $hasil['id_tugas'] ?? null,
            'id_komponen' => $hasil['id_komponen'] ?? null,
            'mode_aktivitas' => ModeAktivitas::Latihan->value,
            // waktu selesai hanya diisi bila latihan berhasil; dipakai sebagai bukti tugas
            'waktu_selesai' => $status === StatusRiwayat::Berhasil->value ? $sekarang : null,
        ]);

        return HasilOperasi::sukses(['idRiwayat' => $riwayat->id_riwayat]);
    }

    /**
     * catatAktivitas(aktivitas: AktivitasBelajar): HasilOperasi  (DPPL 5.3.2, Q-016)
     *
     * Mencatat aktivitas selain latihan, misalnya membaca materi yang menjadi
     * komponen tugas. Kunci wajib: id_pelajar dan mode_aktivitas ('materi', 'tugas', atau
     * 'pengumpulan'); opsional: id_materi, id_tugas, id_komponen, waktu_selesai.
     */
    public static function catatAktivitas(array $aktivitas): HasilOperasi
    {
        foreach (['id_pelajar', 'mode_aktivitas'] as $kunci) {
            if (! array_key_exists($kunci, $aktivitas)) {
                return HasilOperasi::gagal('DATA_TIDAK_LENGKAP', "Kolom {$kunci} wajib diisi");
            }
        }

        $jenis = $aktivitas['mode_aktivitas'] instanceof ModeAktivitas ? $aktivitas['mode_aktivitas']->value : strtolower((string) $aktivitas['mode_aktivitas']);
        if (! in_array($jenis, [ModeAktivitas::Materi->value, ModeAktivitas::Tugas->value, ModeAktivitas::Pengumpulan->value], true)) {
            return HasilOperasi::gagal('AKTIVITAS_TIDAK_VALID', 'Gunakan simpanHasil untuk hasil latihan');
        }

        $sekarang = now();
        $riwayat = self::create([
            'id_pelajar' => $aktivitas['id_pelajar'],
            'id_materi' => $aktivitas['id_materi'] ?? null,
            'tanggal_pengerjaan' => $sekarang,
            'status' => StatusRiwayat::Selesai->value,
            'id_tugas' => $aktivitas['id_tugas'] ?? null,
            'id_komponen' => $aktivitas['id_komponen'] ?? null,
            'mode_aktivitas' => $jenis,
            'waktu_selesai' => $aktivitas['waktu_selesai'] ?? $sekarang,
        ]);

        return HasilOperasi::sukses(['idRiwayat' => $riwayat->id_riwayat]);
    }

    // ------------------------------------------------------------------
    // Operasi pembacaan
    // ------------------------------------------------------------------

    /**
     * cekPernahSelesai(idPelajar, targetSoal, mode): Boolean  (DPPL 5.3.9, Q-009)
     * Dipakai LatihanController agar soal yang sama tidak memberi poin dua kali.
     */
    public static function cekPernahSelesai(int $idPelajar, string $targetSoal, ModeLatihan|string $mode): bool
    {
        $mode = $mode instanceof ModeLatihan ? $mode->value : strtolower($mode);

        return self::query()
            ->where('id_pelajar', $idPelajar)
            ->where('target_soal', $targetSoal)
            ->where('mode', $mode)
            ->where('status', StatusRiwayat::Berhasil->value)
            ->exists();
    }

    /**
     * ambilRiwayat(idPelajar, periode): List<RiwayatLatihan>  (DPPL 5.3.3, Q-011)
     */
    public static function ambilRiwayat(int $idPelajar, Periode $periode): Collection
    {
        return self::query()
            ->with('materi:id_materi,nama_materi,jenis_aksara')
            ->where('id_pelajar', $idPelajar)
            ->whereBetween('tanggal_pengerjaan', $periode->rentang())
            ->orderByDesc('tanggal_pengerjaan')
            ->orderByDesc('id_riwayat')
            ->get();
    }

    /**
     * ambilRiwayatMateri(idPelajar, idMateri): List<RiwayatLatihan>
     * Seluruh riwayat Pelajar pada satu materi, untuk detail hasil di halaman progres.
     */
    public static function ambilRiwayatMateri(int $idPelajar, int $idMateri): Collection
    {
        return self::query()
            ->where('id_pelajar', $idPelajar)
            ->where('id_materi', $idMateri)
            ->orderByDesc('tanggal_pengerjaan')
            ->orderByDesc('id_riwayat')
            ->get();
    }

    /**
     * ambilBuktiPenyelesaian(idPelajar, idTugas): BuktiPenyelesaian  (DPPL 5.3.4, Q-015)
     *
     * Mengembalikan peta id_komponen => waktu selesai terakhir. Percobaan berstatus
     * perlu_diulang tidak dihitung sebagai bukti selesai.
     *
     * @return array<int, string>
     */
    public static function ambilBuktiPenyelesaian(int $idPelajar, int $idTugas): array
    {
        return self::query()
            ->where('id_pelajar', $idPelajar)
            ->where('id_tugas', $idTugas)
            ->whereNotNull('id_komponen')
            ->whereIn('status', [StatusRiwayat::Berhasil->value, StatusRiwayat::Selesai->value])
            ->groupBy('id_komponen')
            ->selectRaw('id_komponen, MAX(waktu_selesai) AS waktu_selesai')
            ->pluck('waktu_selesai', 'id_komponen')
            ->all();
    }

    /**
     * ambilStatusProgres(idPelajar): Data  (DPPL 5.3.5, Q-012)
     *
     * Status belajar per materi, dipakai MateriController (I-04) dan halaman progres:
     *   'belum'   : belum ada riwayat pada materi itu;
     *   'selesai' : rata-rata skor >= ambang tuntas (config ngaksara.materi.ambang_tuntas, bawaan 70);
     *   'sedang'  : sudah ada riwayat tetapi belum tuntas.
     * 'persen' adalah rata-rata skor yang dibulatkan (0-100), 0 bila belum ada skor.
     * Jika $daftarIdMateri diisi, materi tanpa riwayat dalam daftar ikut dikembalikan sebagai 'belum'.
     *
     * @return array<int, array{status: string, persen: int}>
     */
    public static function ambilStatusProgres(int $idPelajar, ?array $daftarIdMateri = null): array
    {
        $ambang = (float) config('ngaksara.materi.ambang_tuntas', 70);

        $query = self::query()
            ->where('id_pelajar', $idPelajar)
            ->whereNotNull('id_materi')
            ->groupBy('id_materi')
            ->selectRaw('id_materi, SUM(skor) AS total_skor, COUNT(skor) AS jumlah_skor');

        if ($daftarIdMateri !== null) {
            $query->whereIn('id_materi', $daftarIdMateri);
        }

        $status = [];
        foreach ($daftarIdMateri ?? [] as $id) {
            $status[(int) $id] = ['status' => 'belum', 'persen' => 0];
        }
        foreach ($query->get() as $baris) {
            $rata = (int) $baris->jumlah_skor > 0 ? (float) $baris->total_skor / (int) $baris->jumlah_skor : 0.0;
            $status[(int) $baris->id_materi] = [
                'status' => $rata >= $ambang ? 'selesai' : 'sedang',
                'persen' => (int) round(min(100, max(0, $rata))),
            ];
        }

        return $status;
    }

    /**
     * hitungPenguasaan(idPelajar): Data  (DPPL 5.3.6, Q-010)
     *
     * Penguasaan per materi = rata-rata skor latihan. Nilai dihitung setiap kali,
     * tidak disimpan. Periode bersifat opsional (perluasan untuk filter halaman progres).
     *
     * @return array<int, float> id_materi => penguasaan (0-100)
     */
    public static function hitungPenguasaan(int $idPelajar, ?Periode $periode = null): array
    {
        $query = self::query()
            ->where('id_pelajar', $idPelajar)
            ->whereNotNull('skor')
            ->whereNotNull('id_materi')
            ->groupBy('id_materi')
            ->selectRaw('id_materi, ROUND(AVG(skor), 2) AS penguasaan');

        if ($periode) {
            $query->whereBetween('tanggal_pengerjaan', $periode->rentang());
        }

        return $query->pluck('penguasaan', 'id_materi')
            ->map(fn ($nilai) => (float) $nilai)
            ->all();
    }

    /**
     * ambilRiwayatPelajar(idPelajar): List<RiwayatLatihan>  (DPPL 5.3.7, Q-014)
     * Seluruh riwayat satu Pelajar, terbaru lebih dulu.
     */
    public static function ambilRiwayatPelajar(int $idPelajar): Collection
    {
        return self::query()
            ->where('id_pelajar', $idPelajar)
            ->orderByDesc('tanggal_pengerjaan')
            ->orderByDesc('id_riwayat')
            ->get();
    }

    /**
     * hitungPolaKesalahan(idPelajar): Data  (DPPL 5.3.8, Q-013)
     * Lima target soal yang paling sering berstatus perlu_diulang.
     *
     * @return array<string, int> target_soal => jumlah salah
     */
    public static function hitungPolaKesalahan(int $idPelajar, ?Periode $periode = null): array
    {
        $query = self::query()
            ->where('id_pelajar', $idPelajar)
            ->where('status', StatusRiwayat::PerluDiulang->value)
            ->whereNotNull('target_soal')
            ->groupBy('target_soal')
            ->selectRaw('target_soal, COUNT(*) AS jumlah_salah')
            ->orderByDesc('jumlah_salah')
            ->orderBy('target_soal')
            ->limit(5);

        if ($periode) {
            $query->whereBetween('tanggal_pengerjaan', $periode->rentang());
        }

        return $query->pluck('jumlah_salah', 'target_soal')
            ->map(fn ($nilai) => (int) $nilai)
            ->all();
    }
}
