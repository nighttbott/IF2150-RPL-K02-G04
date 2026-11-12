<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Bagian C25: daftar komponen yang disimpan dalam transaksi draf tugas. */
class KomponenTugas extends Model
{
    protected $table = 'komponen_tugas';

    protected $primaryKey = 'id_komponen';

    public $timestamps = false;

    protected $fillable = ['id_tugas', 'jenis', 'id_materi', 'mode', 'wajib'];

    protected function casts(): array
    {
        return ['id_tugas' => 'integer', 'id_materi' => 'integer', 'wajib' => 'boolean'];
    }

    public function tugas(): BelongsTo
    {
        return $this->belongsTo(TugasKelas::class, 'id_tugas', 'id_tugas');
    }
}
