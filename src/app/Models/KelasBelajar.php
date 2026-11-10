<?php

namespace App\Models;

use App\Enums\StatusKeanggotaan;
use App\Enums\StatusKelas;
use App\Support\Progres\HasilOperasi;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * C23 KelasBelajar (pemilik modul: Marvel).
 *
 * Kolom: id_kelas, nama, pemilik_id, kode_bergabung (UNIQUE), status, status_scoreboard.
 * Operasi: buatKelas, buatKodeBergabung, gantiKode, ubahScoreboard, daftarAnggota,
 * keluarkanAnggota (Algo-083), ambilScoreboard, daftarTugas, milikPengajar.
 *
 * Tabel keanggotaan_kelas dan tugas_kelas milik modul Abduh dibaca lewat query langsung
 * (seperti PemantauanKelasController) agar berkas ini tidak bergantung pada model yang belum digabung.
 */
class KelasBelajar extends Model
{
    /** Huruf besar dan angka tanpa O, 0, I, 1 agar kode mudah dibaca (Algo-080). */
    public const HURUF_KODE = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

    public const PANJANG_KODE = 6;

    public const MAKS_PERCOBAAN_KODE = 10;

    protected $table = 'kelas_belajar';

    protected $primaryKey = 'id_kelas';

    public $timestamps = false;

    protected $fillable = ['nama', 'pemilik_id', 'kode_bergabung', 'status', 'status_scoreboard'];

    protected function casts(): array
    {
        return [
            'status' => StatusKelas::class,
            'status_scoreboard' => 'boolean',
        ];
    }

    public function pemilik(): BelongsTo
    {
        return $this->belongsTo(AkunPengguna::class, 'pemilik_id', 'id_akun');
    }

    /**
     * buatKodeBergabung(): String  (Algo-080)
     *
     * 6 karakter dari HURUF_KODE dengan random_int. Dicek unik, diulang paling banyak
     * 10 kali; mengembalikan null bila semua percobaan bentrok. Kolom UNIQUE tetap menjadi pengaman.
     */
    public static function buatKodeBergabung(): ?string
    {
        $maks = strlen(self::HURUF_KODE) - 1;

        for ($i = 0; $i < self::MAKS_PERCOBAAN_KODE; $i++) {
            $kode = '';
            for ($j = 0; $j < self::PANJANG_KODE; $j++) {
                $kode .= self::HURUF_KODE[random_int(0, $maks)];
            }
            if (! static::query()->where('kode_bergabung', $kode)->exists()) {
                return $kode;
            }
        }

        return null;
    }

    /**
     * buatKelas(idPemilik, nama): HasilOperasi  (UC-07)
     * Data hasil: idKelas, kodeBergabung.
     */
    public static function buatKelas(int $idPemilik, string $nama): HasilOperasi
    {
        $nama = trim($nama);
        if ($nama === '' || mb_strlen($nama) > 100) {
            return HasilOperasi::gagal('NAMA_TIDAK_VALID', 'Nama kelas wajib diisi (maksimal 100 karakter).');
        }

        // Kolom UNIQUE menangkap balapan dua permintaan yang memilih kode sama; coba lagi dengan kode baru.
        for ($coba = 0; $coba < 3; $coba++) {
            $kode = static::buatKodeBergabung();
            if ($kode === null) {
                return HasilOperasi::gagal('KODE_GAGAL', 'Kode bergabung gagal dibuat. Silakan coba lagi.');
            }

            try {
                $kelas = static::query()->create([
                    'nama' => $nama,
                    'pemilik_id' => $idPemilik,
                    'kode_bergabung' => $kode,
                    'status' => StatusKelas::Aktif->value,
                    'status_scoreboard' => false,
                ]);
            } catch (UniqueConstraintViolationException) {
                continue;
            }

            return HasilOperasi::sukses(['idKelas' => (int) $kelas->id_kelas, 'kodeBergabung' => $kode], 'Kelas dibuat.');
        }

        return HasilOperasi::gagal('KODE_GAGAL', 'Kode bergabung gagal dibuat. Silakan coba lagi.');
    }

    /**
     * Kelas milik seorang Pengajar beserta jumlah anggota aktif (atribut jumlah_anggota).
     *
     * @return Collection<int, KelasBelajar>
     */
    public static function milikPengajar(int $idPengajar): Collection
    {
        return static::query()
            ->select('kelas_belajar.*')
            ->selectSub(
                DB::table('keanggotaan_kelas')
                    ->selectRaw('COUNT(*)')
                    ->whereColumn('keanggotaan_kelas.id_kelas', 'kelas_belajar.id_kelas')
                    ->where('keanggotaan_kelas.status', StatusKeanggotaan::Aktif->value),
                'jumlah_anggota'
            )
            ->where('pemilik_id', $idPengajar)
            ->orderBy('nama')
            ->get();
    }

    /**
     * gantiKode(): HasilOperasi
     * Kode lama langsung tidak berlaku; anggota yang sudah bergabung tidak dikeluarkan.
     */
    public function gantiKode(): HasilOperasi
    {
        for ($coba = 0; $coba < 3; $coba++) {
            $kode = static::buatKodeBergabung();
            if ($kode === null) {
                break;
            }

            try {
                $this->kode_bergabung = $kode;
                $this->save();
            } catch (UniqueConstraintViolationException) {
                continue;
            }

            return HasilOperasi::sukses(['kodeBergabung' => $kode], 'Kode bergabung diganti. Kode lama tidak berlaku lagi.');
        }

        $this->refresh();

        return HasilOperasi::gagal('KODE_GAGAL', 'Kode bergabung gagal dibuat. Silakan coba lagi.');
    }

    /** ubahScoreboard(aktif): HasilOperasi, menyalakan atau mematikan papan peringkat kelas. */
    public function ubahScoreboard(bool $aktif): HasilOperasi
    {
        $this->status_scoreboard = $aktif;
        $this->save();

        return HasilOperasi::sukses(['aktif' => $aktif], $aktif ? 'Papan peringkat dinyalakan.' : 'Papan peringkat dimatikan.');
    }

    /**
     * Semua anggota kelas (aktif lebih dulu), lengkap dengan poin untuk Pengajar.
     * Setiap baris: id_pelajar, nama_tampilan, email, status, poin.
     *
     * @return Collection<int, object>
     */
    public function daftarAnggota(): Collection
    {
        return DB::table('keanggotaan_kelas as k')
            ->join('akun_pengguna as a', 'a.id_akun', '=', 'k.id_pelajar')
            ->leftJoin('motivasi_belajar as m', 'm.id_pelajar', '=', 'k.id_pelajar')
            ->where('k.id_kelas', $this->id_kelas)
            ->orderByRaw("CASE k.status WHEN 'aktif' THEN 0 ELSE 1 END")
            ->orderBy('a.nama_tampilan')
            ->get(['k.id_pelajar', 'a.nama_tampilan', 'a.email', 'k.status', 'm.poin']);
    }

    /**
     * Peta id_pelajar => nama_tampilan untuk anggota aktif.
     *
     * @return array<int, string>
     */
    public function anggotaAktif(): array
    {
        return $this->daftarAnggota()
            ->where('status', StatusKeanggotaan::Aktif->value)
            ->mapWithKeys(fn ($baris) => [(int) $baris->id_pelajar => $baris->nama_tampilan])
            ->all();
    }

    /**
     * keluarkanAnggota(idPelajar): HasilOperasi  (Algo-083)
     * Mengubah status menjadi keluar; baris keanggotaan dan riwayat latihan tidak dihapus.
     * Pemeriksaan pemilik dilakukan pemanggil lewat KelasBelajarPolicy::kelola.
     */
    public function keluarkanAnggota(int $idPelajar): HasilOperasi
    {
        $diubah = DB::table('keanggotaan_kelas')
            ->where('id_kelas', $this->id_kelas)
            ->where('id_pelajar', $idPelajar)
            ->where('status', StatusKeanggotaan::Aktif->value)
            ->update(['status' => StatusKeanggotaan::Keluar->value]);

        if ($diubah === 0) {
            return HasilOperasi::gagal('BUKAN_ANGGOTA_AKTIF', 'Pelajar ini bukan anggota aktif kelas.');
        }

        return HasilOperasi::sukses(['idPelajar' => $idPelajar], 'Anggota dikeluarkan dari kelas.');
    }

    /**
     * Papan peringkat anggota aktif (memakai MotivasiBelajar::susunScoreboard).
     * Kosong bila scoreboard dimatikan.
     *
     * @return array<int, array{peringkat:int, id_pelajar:int, nama_tampilan:string, poin:int}>
     */
    public function ambilScoreboard(): array
    {
        if (! $this->status_scoreboard) {
            return [];
        }

        return MotivasiBelajar::susunScoreboard($this->anggotaAktif());
    }

    /**
     * Tugas kelas, terbaru lebih dulu, dengan jumlah komponen dan jumlah pengumpulan.
     *
     * @return Collection<int, object>
     */
    public function daftarTugas(): Collection
    {
        return DB::table('tugas_kelas as t')
            ->select('t.*')
            ->selectSub(
                DB::table('komponen_tugas')->selectRaw('COUNT(*)')->whereColumn('komponen_tugas.id_tugas', 't.id_tugas'),
                'jumlah_komponen'
            )
            ->selectSub(
                DB::table('penyelesaian_tugas')->selectRaw('COUNT(*)')->whereColumn('penyelesaian_tugas.id_tugas', 't.id_tugas'),
                'jumlah_kumpul'
            )
            ->where('t.id_kelas', $this->id_kelas)
            ->orderByDesc('t.id_tugas')
            ->get();
    }
}
