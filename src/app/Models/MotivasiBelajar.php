<?php

namespace App\Models;

use App\Support\Progres\HasilOperasi;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;

/**
 * C22 MotivasiBelajar (pemilik modul: Natan).
 *
 * Menyimpan streak, poin, dan badge satu Pelajar (DPPL2 subbab 5.22,
 * Algo-028 sampai Algo-033). Satu baris per Pelajar.
 *
 * Cara pakai dari LatihanController (Algo-026), di dalam satu transaksi:
 *   $m = MotivasiBelajar::milikPelajar($idPelajar, kunci: true);
 *   $m->perbaruiStreak(today());
 *   if (! $pernah) $m->tambahPoin($idRiwayat, 10);
 *   $m->berikanBadge('pemula');
 */
class MotivasiBelajar extends Model
{
    /**
     * Daftar badge yang dikenal beserta nama tampilannya. Syarat pemberian badge
     * diperiksa oleh pemanggil (LatihanController.periksaBadge, modul Naufal).
     * Tambahkan kode baru di sini agar nama tampilannya muncul di halaman progres.
     */
    public const BADGE = [
        'pemula' => 'Pemula Aksara',
        'konsisten_7' => 'Rajin 7 Hari',
        'seratus_poin' => 'Seratus Poin',
    ];

    protected $table = 'motivasi_belajar';

    protected $primaryKey = 'id_pelajar';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = ['id_pelajar', 'streak', 'poin', 'tanggal_aktif_terakhir', 'daftar_badge'];

    protected $attributes = ['streak' => 0, 'poin' => 0];

    protected function casts(): array
    {
        return [
            'streak' => 'integer',
            'poin' => 'integer',
            'tanggal_aktif_terakhir' => 'date',
            'daftar_badge' => 'array',
        ];
    }

    public function pelajar(): BelongsTo
    {
        return $this->belongsTo(AkunPengguna::class, 'id_pelajar', 'id_akun');
    }

    /**
     * Mengambil (atau membuat) baris motivasi seorang Pelajar.
     * Bila $kunci = true, baris dikunci (SELECT ... FOR UPDATE, DPPL Q-026) sehingga
     * dua permintaan bersamaan tidak menggandakan poin. Harus dipanggil di dalam
     * DB::transaction agar penguncian berlaku.
     */
    public static function milikPelajar(int $idPelajar, bool $kunci = false): self
    {
        self::query()->firstOrCreate(['id_pelajar' => $idPelajar], ['streak' => 0, 'poin' => 0, 'daftar_badge' => []]);

        $query = self::query()->where('id_pelajar', $idPelajar);

        return $kunci ? $query->lockForUpdate()->firstOrFail() : $query->firstOrFail();
    }

    /** Daftar badge yang dimiliki (NULL di basis data dibaca sebagai daftar kosong). */
    public function badge(): array
    {
        return $this->daftar_badge ?? [];
    }

    /**
     * perbaruiStreak(tanggal: Date): void  (Algo-028, Q-028)
     *
     * - aktivitas di hari yang sama: streak tidak berubah;
     * - aktivitas sehari setelah aktivitas terakhir: streak + 1;
     * - ada hari terlewat atau aktivitas pertama: streak = 1.
     * Dipanggil hanya setelah latihan berstatus berhasil.
     */
    public function perbaruiStreak(CarbonInterface|string $tanggal): void
    {
        $hariIni = CarbonImmutable::parse($tanggal)->startOfDay();
        $terakhir = $this->tanggal_aktif_terakhir ? CarbonImmutable::parse($this->tanggal_aktif_terakhir)->startOfDay() : null;

        if ($terakhir && $terakhir->equalTo($hariIni)) {
            return;
        }

        if ($terakhir && $terakhir->addDay()->equalTo($hariIni)) {
            $this->streak = $this->streak + 1;
        } else {
            $this->streak = 1;
        }

        $this->tanggal_aktif_terakhir = $hariIni->toDateString();
        $this->save();
    }

    /**
     * tambahPoin(idAktivitas: Integer, nilai: Integer): HasilOperasi  (Algo-029, Q-029)
     *
     * Penambahan dilakukan secara atomik di basis data (poin = poin + nilai).
     * Pencegahan poin ganda untuk soal yang sama dijaga pemanggil lewat
     * RiwayatLatihan::cekPernahSelesai().
     */
    public function tambahPoin(int $idAktivitas, int $nilai): HasilOperasi
    {
        if ($nilai <= 0) {
            return HasilOperasi::gagal('NILAI_TIDAK_VALID', 'Poin harus lebih dari 0');
        }

        $baris = self::query()->where('id_pelajar', $this->id_pelajar)->increment('poin', $nilai);
        if ($baris === 0) {
            return HasilOperasi::gagal('TIDAK_DITEMUKAN', 'Data motivasi pelajar tidak ditemukan');
        }

        $this->poin = (int) self::query()->where('id_pelajar', $this->id_pelajar)->value('poin');
        $this->syncOriginalAttribute('poin');

        return HasilOperasi::sukses(['poin' => $this->poin, 'idAktivitas' => $idAktivitas]);
    }

    /**
     * berikanBadge(kode: String): HasilOperasi  (Algo-030, Q-030)
     * Badge yang sudah dimiliki tidak disimpan ulang.
     */
    public function berikanBadge(string $kode): HasilOperasi
    {
        $daftar = $this->badge();
        if (in_array($kode, $daftar, true)) {
            return HasilOperasi::sukses(['baru' => false]);
        }

        $daftar[] = $kode;
        $this->daftar_badge = $daftar;
        $this->save();

        return HasilOperasi::sukses(['baru' => true]);
    }

    /**
     * ambilMotivasi(idPelajar): Data  (Algo-032, Q-032)
     *
     * Streak yang sudah terputus (aktivitas terakhir sebelum kemarin) ditampilkan 0
     * sampai latihan berikutnya, tanpa mengubah data tersimpan.
     */
    public static function ambilMotivasi(int $idPelajar): array
    {
        $baris = self::query()->find($idPelajar);
        if (! $baris) {
            return ['streak' => 0, 'poin' => 0, 'tanggalAktifTerakhir' => null, 'daftarBadge' => []];
        }

        $streak = $baris->streak;
        $terakhir = $baris->tanggal_aktif_terakhir ? CarbonImmutable::parse($baris->tanggal_aktif_terakhir)->startOfDay() : null;
        if ($terakhir === null || $terakhir->lessThan(CarbonImmutable::today()->subDay())) {
            $streak = 0;
        }

        return [
            'streak' => $streak,
            'poin' => $baris->poin,
            'tanggalAktifTerakhir' => $terakhir?->toDateString(),
            'daftarBadge' => $baris->badge(),
        ];
    }

    /**
     * ambilPoinKelas(anggotaAktif: List<Integer>): List<Peringkat>  (Algo-033, Q-033)
     *
     * Poin seluruh anggota aktif, terurut poin menurun lalu id_pelajar menaik.
     * Anggota yang belum punya baris motivasi dianggap berpoin 0.
     *
     * @return Collection<int, array{id_pelajar:int, poin:int}>
     */
    public static function ambilPoinKelas(array $anggotaAktif): Collection
    {
        if ($anggotaAktif === []) {
            return collect();
        }

        $poin = self::query()->whereIn('id_pelajar', $anggotaAktif)->pluck('poin', 'id_pelajar');

        return collect($anggotaAktif)
            ->map(fn ($id) => ['id_pelajar' => (int) $id, 'poin' => (int) ($poin[$id] ?? 0)])
            ->sortBy([['poin', 'desc'], ['id_pelajar', 'asc']])
            ->values();
    }

    /**
     * susunScoreboard(anggotaAktif: List<Profil>): List<Peringkat>  (Algo-031)
     *
     * $anggotaAktif berupa peta id_pelajar => nama_tampilan. Poin sama mendapat
     * peringkat sama (contoh: 1, 2, 2, 4). Hanya nama tampilan dan poin yang
     * dikembalikan, tanpa data pribadi lain.
     *
     * @param  array<int, string>  $anggotaAktif
     * @return array<int, array{peringkat:int, id_pelajar:int, nama_tampilan:string, poin:int}>
     */
    public static function susunScoreboard(array $anggotaAktif): array
    {
        $hasil = [];
        $peringkat = 0;
        $poinSebelumnya = null;
        $urutan = 0;

        foreach (self::ambilPoinKelas(array_keys($anggotaAktif)) as $baris) {
            $urutan++;
            if ($poinSebelumnya === null || $baris['poin'] < $poinSebelumnya) {
                $peringkat = $urutan;
            }
            $hasil[] = [
                'peringkat' => $peringkat,
                'id_pelajar' => $baris['id_pelajar'],
                'nama_tampilan' => $anggotaAktif[$baris['id_pelajar']],
                'poin' => $baris['poin'],
            ];
            $poinSebelumnya = $baris['poin'];
        }

        return $hasil;
    }
}
