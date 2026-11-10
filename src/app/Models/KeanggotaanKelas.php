<?php

namespace App\Models;

use App\Enums\StatusKeanggotaan;
use App\Support\Progres\HasilOperasi;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Throwable;

/**
 * C24 KeanggotaanKelas (tabel keanggotaan_kelas). Operasi yang dipakai UC-07 (Marvel):
 * ubahStatus (Algo-111) dan daftarAnggotaAktif (Algo-112). simpanKeanggotaan (Algo-109),
 * periksaAkses (Algo-110) dan daftarAnggota milik Abduh ditambahkan pada kelas yang sama.
 * Pengajar mengeluarkan anggota dengan status `nonaktif`; baris dan riwayat belajar tidak dihapus.
 */
class KeanggotaanKelas extends Model
{
    protected $table = 'keanggotaan_kelas';

    protected $primaryKey = 'id_keanggotaan';

    public $timestamps = false;

    protected $fillable = ['id_kelas', 'id_pelajar', 'status'];

    /** Algo-112 */
    public static function daftarAnggotaAktif(int $idKelas): Collection
    {
        return static::query()
            ->where('id_kelas', $idKelas)
            ->where('status', StatusKeanggotaan::Aktif->value)
            ->orderBy('id_keanggotaan')
            ->get();
    }

    /** Pembantu pencarian satu keanggotaan (bukan operasi DPPL). */
    public static function cari(int $idKelas, int $idPelajar): ?self
    {
        return static::query()->where('id_kelas', $idKelas)->where('id_pelajar', $idPelajar)->first();
    }

    /** Algo-111 */
    public function ubahStatus(string $status): HasilOperasi
    {
        if (! in_array($status, StatusKeanggotaan::nilai(), true)) {
            return HasilOperasi::gagal('STATUS_TIDAK_VALID', 'Status keanggotaan tidak dikenal.');
        }

        try {
            $this->status = $status;
            $this->save();
        } catch (Throwable) {
            return HasilOperasi::gagal('GAGAL_SIMPAN', 'Status anggota gagal diubah.');
        }

        return HasilOperasi::sukses(['status' => $status]);
    }
}
