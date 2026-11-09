<?php

namespace App\Models;

use App\Support\Kelas\HasilOperasi;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator as DataValidator;
use Throwable;

/** C25. Controller Pengajar wajib memeriksa peran dan kepemilikan sebelum mutasi. */
class TugasKelas extends Model
{
    protected $table = 'tugas_kelas';

    protected $primaryKey = 'id_tugas';

    public $timestamps = false;

    protected $fillable = ['id_kelas', 'instruksi', 'tenggat', 'status'];

    protected function casts(): array
    {
        return ['id_kelas' => 'integer', 'tenggat' => 'immutable_datetime'];
    }

    public function komponen(): HasMany
    {
        return $this->hasMany(KomponenTugas::class, 'id_tugas', 'id_tugas')->orderBy('id_komponen');
    }

    /**
     * Algo-113. id_tugas hanya diisi untuk memperbarui draf pada kelas yang sama.
     *
     * @param  array{id_kelas?: int, id_tugas?: int, instruksi?: string, tenggat?: string, komponen_tugas?: array}  $data
     */
    public function simpan(array $data): HasilOperasi
    {
        try {
            return DB::transaction(function () use ($data): HasilOperasi {
                $validator = self::validatorData($data);
                if ($validator->fails()) {
                    return HasilOperasi::gagal('dataTidakValid', 'Periksa instruksi, tenggat, dan komponen tugas.', [
                        'galat' => $validator->errors()->toArray(),
                    ]);
                }
                $kelas = DB::table('kelas_belajar')->where('id_kelas', $data['id_kelas'])
                    ->lockForUpdate()->first();
                if ($kelas === null || strtolower($kelas->status) !== 'aktif') {
                    return HasilOperasi::gagal('kelasTidakAktif', 'Tugas hanya dapat disimpan pada kelas aktif.');
                }
                if (isset($data['id_tugas'])) {
                    $tugas = self::query()->whereKey($data['id_tugas'])->where('id_kelas', $data['id_kelas'])
                        ->lockForUpdate()->first();
                    if ($tugas === null || $tugas->status !== 'draf') {
                        return HasilOperasi::gagal('tugasTidakDapatDiubah', 'Hanya draf pada kelas yang sama dapat diubah.');
                    }
                } else {
                    $tugas = new self;
                }
                $tugas->fill([
                    'id_kelas' => (int) $data['id_kelas'],
                    'instruksi' => trim($data['instruksi']),
                    'tenggat' => CarbonImmutable::parse($data['tenggat'], config('app.timezone'))
                        ->setTimezone(config('app.timezone')),
                    'status' => 'draf',
                ]);
                $tugas->saveOrFail();
                $tugas->komponen()->delete();
                foreach ($data['komponen_tugas'] as $komponen) {
                    $tugas->komponen()->create([
                        'jenis' => $komponen['jenis'],
                        'id_materi' => (int) $komponen['id_materi'],
                        'mode' => $komponen['mode'] ?? null,
                        'wajib' => (bool) $komponen['wajib'],
                    ]);
                }

                return HasilOperasi::sukses('drafDisimpan', 'Draf tugas berhasil disimpan.', ['id_tugas' => $tugas->getKey()]);
            }, 3);
        } catch (Throwable $error) {
            report($error);

            return HasilOperasi::gagal('gagalSimpan', 'Draf gagal disimpan. Tidak ada perubahan yang diterapkan.');
        }
    }

    /** Algo-114. Idempoten; penerbitan ulang tidak mengubah tenggat/komponen. */
    public function terbitkan(): HasilOperasi
    {
        if (! $this->exists) {
            return HasilOperasi::gagal('tugasTidakDitemukan', 'Draf tugas belum tersimpan.');
        }
        try {
            return DB::transaction(function (): HasilOperasi {
                $kelas = DB::table('kelas_belajar')->where('id_kelas', $this->id_kelas)
                    ->lockForUpdate()->first();
                if ($kelas === null || strtolower($kelas->status) !== 'aktif') {
                    return HasilOperasi::gagal('kelasTidakAktif', 'Kelas tidak ditemukan atau tidak aktif.');
                }
                $tugas = self::query()->whereKey($this->getKey())->where('id_kelas', $this->id_kelas)
                    ->lockForUpdate()->first();
                if ($tugas === null) {
                    return HasilOperasi::gagal('tugasTidakDitemukan', 'Draf tugas tidak ditemukan.');
                }
                if ($tugas->status === 'terbit') {
                    return HasilOperasi::sukses('sudahTerbit', 'Tugas sudah diterbitkan.', ['id_tugas' => $tugas->getKey()]);
                }
                if ($tugas->status !== 'draf') {
                    return HasilOperasi::gagal('tugasTidakDapatDiubah', 'Status tugas tidak dapat diterbitkan.');
                }
                $komponen = $tugas->komponen()->lockForUpdate()->get();
                $validator = self::validatorData([
                    'id_kelas' => $tugas->id_kelas,
                    'instruksi' => $tugas->instruksi,
                    'tenggat' => $tugas->tenggat->toIso8601String(),
                    'komponen_tugas' => $komponen->map(fn (KomponenTugas $item): array => [
                        'jenis' => $item->jenis, 'id_materi' => $item->id_materi,
                        'mode' => $item->mode, 'wajib' => $item->wajib,
                    ])->all(),
                ]);
                if ($validator->fails()) {
                    return HasilOperasi::gagal('dataTidakValid', 'Draf belum memenuhi syarat penerbitan.', [
                        'galat' => $validator->errors()->toArray(),
                    ]);
                }
                $tugas->status = 'terbit';
                $tugas->saveOrFail();

                return HasilOperasi::sukses('tugasDiterbitkan', 'Tugas berhasil diterbitkan.', ['id_tugas' => $tugas->getKey()]);
            }, 3);
        } catch (Throwable $error) {
            report($error);

            return HasilOperasi::gagal('gagalSimpan', 'Penerbitan tugas gagal. Silakan coba lagi.');
        }
    }

    /** Q-C25-01: lewat tenggat tetap dapat dikerjakan. */
    public static function ambilTugasAktif(int $idKelas): Collection
    {
        return self::query()->where('id_kelas', $idKelas)->where('status', 'terbit')
            ->with('komponen')->orderBy('tenggat')->orderBy('id_tugas')->get();
    }

    /** Pemanggil wajib memeriksa keanggotaan aktif sebelum membaca detail. */
    public static function ambilDetail(int $idKelas, int $idTugas): ?self
    {
        return self::query()->where('id_kelas', $idKelas)->whereKey($idTugas)->where('status', 'terbit')
            ->with('komponen')->first();
    }

    private static function validatorData(array $data): DataValidator
    {
        $validator = Validator::make($data, [
            'id_kelas' => ['required', 'integer', 'min:1'],
            'id_tugas' => ['sometimes', 'required', 'integer', 'min:1'],
            'instruksi' => ['required', 'string', 'max:10000'],
            'tenggat' => ['required', 'date', 'after:now'],
            'komponen_tugas' => ['required', 'array', 'list', 'min:1', 'max:100'],
            'komponen_tugas.*' => ['required', 'array'],
            'komponen_tugas.*.jenis' => ['required', Rule::in(['materi', 'latihan'])],
            'komponen_tugas.*.id_materi' => ['required', 'integer', 'min:1',
                Rule::exists('materi_aksara', 'id_materi')->where(
                    fn (Builder $query): Builder => $query->whereRaw('LOWER(status_publikasi) = ?', ['terbit']),
                )],
            'komponen_tugas.*.mode' => ['nullable', 'string'],
            'komponen_tugas.*.wajib' => ['required', 'boolean'],
        ]);
        $validator->after(function (DataValidator $validator) use ($data): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }
            $jumlahWajib = 0;
            foreach ($data['komponen_tugas'] as $index => $komponen) {
                $jumlahWajib += (int) (bool) $komponen['wajib'];
                $mode = $komponen['mode'] ?? null;
                if (($komponen['jenis'] === 'materi' && $mode !== null)
                    || ($komponen['jenis'] === 'latihan' && ! in_array($mode, ['menulis', 'bunyi', 'merangkai', 'transliterasi'], true))) {
                    $validator->errors()->add("komponen_tugas.$index.mode", 'Mode tidak sesuai jenis komponen.');
                }
            }
            if ($jumlahWajib === 0) {
                $validator->errors()->add('komponen_tugas', 'Minimal satu komponen wajib diperlukan.');
            }
        });

        return $validator;
    }
}
