<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;
use InvalidArgumentException;

/** C26: pemetaan data, ketepatan, dan pembacaan status. Penyimpanan atomik beserta verifikasi bukti ditangani layanan PengumpulanTugas. */
class PenyelesaianTugas extends Model
{
    protected $table = 'penyelesaian_tugas';

    protected $primaryKey = 'id_penyelesaian';

    public $timestamps = false;

    protected $guarded = ['*'];

    protected function casts(): array
    {
        return ['id_tugas' => 'integer', 'id_pelajar' => 'integer', 'waktu_kumpul' => 'immutable_datetime'];
    }

    public function tugas(): BelongsTo
    {
        return $this->belongsTo(TugasKelas::class, 'id_tugas', 'id_tugas');
    }

    /** Algo-119. Waktu input harus berasal dari bukti server yang sudah diverifikasi. */
    public static function tentukanStatusKetepatan(DateTimeInterface $waktuSelesai, DateTimeInterface $tenggat): string
    {
        return $waktuSelesai <= $tenggat ? 'tepat_waktu' : 'terlambat';
    }

    /** Algo-121/Q-C26-04. Pemanggil menyediakan ID tugas dari kelas yang boleh diakses. @param  list<int>  $daftarTugas  Daftar ID, bukan model atau payload request mentah. @return Collection<int, array{id_tugas: int, status: string, waktu_kumpul: mixed}> */
    public static function ambilStatus(int $idPelajar, array $daftarTugas): Collection
    {
        foreach ($daftarTugas as $idTugas) {
            if (! is_int($idTugas) || $idTugas < 1) {
                throw new InvalidArgumentException('Daftar tugas harus berisi ID integer positif.');
            }
        }
        if ($daftarTugas === []) {
            return collect();
        }
        $tersimpan = self::query()->where('id_pelajar', $idPelajar)->whereIn('id_tugas', $daftarTugas)
            ->get(['id_tugas', 'waktu_kumpul', 'status'])->keyBy('id_tugas');

        return collect(array_map(function (int $idTugas) use ($tersimpan): array {
            $hasil = $tersimpan->get($idTugas);

            return [
                'id_tugas' => $idTugas,
                'status' => $hasil?->status ?? 'belum_selesai',
                'waktu_kumpul' => $hasil?->waktu_kumpul,
            ];
        }, $daftarTugas));
    }
}
