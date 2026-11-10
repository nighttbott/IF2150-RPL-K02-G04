<?php

namespace App\Models;

use App\Support\Kelas\HasilOperasi;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Throwable;

/** C24. Operasi internal; controller wajib memeriksa peran/kepemilikan kelas. */
class KeanggotaanKelas extends Model
{
    protected $table = 'keanggotaan_kelas';

    protected $primaryKey = 'id_keanggotaan';

    public $timestamps = false;

    protected $fillable = ['id_kelas', 'id_pelajar', 'status'];

    protected function casts(): array
    {
        return ['id_kelas' => 'integer', 'id_pelajar' => 'integer'];
    }

    /** Algo-109; anggota nonaktif diaktifkan kembali sesuai keputusan M9. */
    public function simpanKeanggotaan(int $idPelajar, int $idKelas): HasilOperasi
    {
        try {
            return DB::transaction(function () use ($idPelajar, $idKelas): HasilOperasi {
                $kelas = DB::table('kelas_belajar')->where('id_kelas', $idKelas)->lockForUpdate()->first();
                if ($kelas === null || strtolower($kelas->status) !== 'aktif') {
                    return HasilOperasi::gagal('kelasTidakAktif', 'Kelas tidak ditemukan atau tidak aktif.');
                }
                $pelajar = DB::table('akun_pengguna')->where('id_akun', $idPelajar)->first();
                if ($pelajar === null || strtolower($pelajar->peran) !== 'pelajar') {
                    return HasilOperasi::gagal('pelajarTidakValid', 'Akun Pelajar tidak ditemukan.');
                }
                $anggota = self::query()->where('id_kelas', $idKelas)
                    ->where('id_pelajar', $idPelajar)->lockForUpdate()->first();
                $kode = 'sudahAnggota';
                if ($anggota === null) {
                    $anggota = self::query()->create([
                        'id_kelas' => $idKelas, 'id_pelajar' => $idPelajar, 'status' => 'aktif',
                    ]);
                    if (! $anggota->exists) {
                        throw new \RuntimeException('Penyimpanan keanggotaan dibatalkan.');
                    }
                    $kode = 'berhasilBergabung';
                } elseif ($anggota->status !== 'aktif') {
                    $anggota->status = 'aktif';
                    if (! $anggota->saveOrFail()) {
                        throw new \RuntimeException('Aktivasi keanggotaan dibatalkan.');
                    }
                    $kode = 'anggotaDiaktifkan';
                }

                return HasilOperasi::sukses($kode, match ($kode) {
                    'sudahAnggota' => 'Kamu sudah menjadi anggota kelas ini.',
                    'anggotaDiaktifkan' => 'Keanggotaan kelas diaktifkan kembali.',
                    default => 'Berhasil bergabung ke kelas.',
                }, ['id_keanggotaan' => $anggota->getKey(), 'id_kelas' => $idKelas]);
            }, 3);
        } catch (Throwable $error) {
            report($error);

            return HasilOperasi::gagal('gagalSimpan', 'Keanggotaan gagal disimpan. Silakan coba lagi.');
        }
    }

    /** Q-C24-01; status kelas diperiksa pula pada controller pembuka kelas. */
    public static function periksaAkses(int $idPelajar, int $idKelas): bool
    {
        return self::query()->where('id_pelajar', $idPelajar)->where('id_kelas', $idKelas)
            ->where('status', 'aktif')->exists();
    }

    public static function daftarAnggotaAktif(int $idKelas): Collection
    {
        return self::query()->where('id_kelas', $idKelas)->where('status', 'aktif')
            ->orderBy('id_keanggotaan')->get();
    }

    public static function daftarAnggota(int $idKelas): Collection
    {
        return self::query()->where('id_kelas', $idKelas)->orderBy('id_keanggotaan')->get();
    }

    /** Pemanggil wajib sudah mengotorisasi Pengajar pemilik kelas (Algo-111). */
    public function ubahStatus(string $status): HasilOperasi
    {
        if (! $this->exists || ! in_array($status, ['aktif', 'keluar', 'nonaktif'], true)) {
            return HasilOperasi::gagal('statusTidakValid', 'Keanggotaan atau status tidak valid.');
        }
        try {
            $hasil = DB::transaction(function () use ($status): HasilOperasi {
                $kelas = DB::table('kelas_belajar')->where('id_kelas', $this->id_kelas)
                    ->lockForUpdate()->first();
                if ($kelas === null || ($status === 'aktif' && strtolower($kelas->status) !== 'aktif')) {
                    return HasilOperasi::gagal('kelasTidakAktif', 'Kelas tidak ditemukan atau tidak aktif.');
                }
                $anggota = self::query()->whereKey($this->getKey())->where('id_kelas', $this->id_kelas)
                    ->lockForUpdate()->first();
                if ($anggota === null) {
                    return HasilOperasi::gagal('anggotaTidakDitemukan', 'Anggota tidak ditemukan.');
                }
                $anggota->status = $status;
                if (! $anggota->saveOrFail()) {
                    throw new \RuntimeException('Perubahan status keanggotaan dibatalkan.');
                }

                return HasilOperasi::sukses('statusDiubah', 'Status keanggotaan diperbarui.');
            }, 3);
            if ($hasil->berhasil) {
                $this->refresh();
            }

            return $hasil;
        } catch (Throwable $error) {
            report($error);

            return HasilOperasi::gagal('gagalSimpan', 'Status keanggotaan gagal disimpan.');
        }
    }
}
