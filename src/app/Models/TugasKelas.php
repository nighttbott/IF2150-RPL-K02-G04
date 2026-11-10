<?php

namespace App\Models;

use App\Enums\StatusTugas;
use App\Support\Progres\HasilOperasi;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * C25 TugasKelas (tabel tugas_kelas + komponen_tugas). Operasi yang dipakai UC-07 (Marvel):
 * simpan (Algo-113), terbitkan (Algo-114) dan ambilTugasAktif (Algo-117). verifikasiDanSimpan
 * (Algo-115), periksaKomponenWajib (Algo-116), ambilStatus dan ambilDetail milik Abduh (UC-05).
 */
class TugasKelas extends Model
{
    protected $table = 'tugas_kelas';

    protected $primaryKey = 'id_tugas';

    public $timestamps = false;

    protected $fillable = ['id_kelas', 'instruksi', 'tenggat', 'status'];

    protected function casts(): array
    {
        return ['tenggat' => 'datetime'];
    }

    /**
     * Algo-113: menyimpan tugas berstatus draf beserta komponennya dalam satu transaksi.
     * $data: id_kelas, instruksi, tenggat, komponen = list [materi => id_materi, jenis => materi|menulis|bunyi|
     * merangkai|transliterasi, wajib => bool]. Jenis "materi" disimpan sebagai jenis=materi tanpa mode;
     * jenis lainnya sebagai jenis=latihan dengan mode tersebut.
     */
    public function simpan(array $data): HasilOperasi
    {
        $komponen = $data['komponen'] ?? [];
        if ($komponen === []) {
            return HasilOperasi::gagal('KOMPONEN_KOSONG', 'Tambahkan minimal satu komponen tugas.');
        }

        try {
            DB::transaction(function () use ($data, $komponen) {
                $this->fill([
                    'id_kelas' => $data['id_kelas'],
                    'instruksi' => $data['instruksi'],
                    'tenggat' => $data['tenggat'],
                    'status' => StatusTugas::Draf->value,
                ])->save();

                DB::table('komponen_tugas')->insert(array_map(fn (array $k) => [
                    'id_tugas' => $this->id_tugas,
                    'jenis' => $k['jenis'] === 'materi' ? 'materi' : 'latihan',
                    'id_materi' => $k['materi'],
                    'mode' => $k['jenis'] === 'materi' ? null : $k['jenis'],
                    'wajib' => (bool) $k['wajib'],
                ], array_values($komponen)));
            });
        } catch (Throwable) {
            return HasilOperasi::gagal('GAGAL_SIMPAN', 'Tugas gagal disimpan.');
        }

        return HasilOperasi::sukses(['idTugas' => $this->id_tugas, 'status' => StatusTugas::Draf->value]);
    }

    /** Algo-114: hanya tugas draf yang memiliki komponen yang dapat diterbitkan. */
    public function terbitkan(): HasilOperasi
    {
        if ($this->status !== StatusTugas::Draf->value) {
            return HasilOperasi::gagal('STATUS_TIDAK_VALID', 'Tugas ini sudah diterbitkan.');
        }
        if (! DB::table('komponen_tugas')->where('id_tugas', $this->id_tugas)->exists()) {
            return HasilOperasi::gagal('KOMPONEN_KOSONG', 'Tugas belum memiliki komponen.');
        }

        try {
            $this->status = StatusTugas::Terbit->value;
            $this->save();
        } catch (Throwable) {
            return HasilOperasi::gagal('GAGAL_SIMPAN', 'Tugas gagal diterbitkan.');
        }

        return HasilOperasi::sukses(['status' => StatusTugas::Terbit->value]);
    }

    /** Algo-117: tugas berstatus terbit pada sebuah kelas, tenggat terdekat lebih dulu. */
    public static function ambilTugasAktif(int $idKelas): array
    {
        return static::query()
            ->where('id_kelas', $idKelas)
            ->where('status', StatusTugas::Terbit->value)
            ->orderBy('tenggat')
            ->get()
            ->all();
    }
}
