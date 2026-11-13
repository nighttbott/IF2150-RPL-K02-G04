<?php

namespace App\Models;

use App\Enums\KategoriFeedback;
use App\Enums\StatusFeedback;
use App\Support\HasilOperasi;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;

/** C04 DataFeedback */
class DataFeedback extends Model
{
    /** urutan status */
    private const URUTAN = [
        'baru'     => 0,
        'ditinjau' => 1,
        'selesai'  => 2,
    ];

    protected $table = 'data_feedback';

    protected $primaryKey = 'id_feedback';

    public $timestamps = false;

    protected $fillable = ['id_akun', 'kategori', 'pesan', 'waktu_kirim', 'status'];

    protected function casts(): array
    {
        return [
            'kategori'    => KategoriFeedback::class,
            'status'      => StatusFeedback::class,
            'waktu_kirim' => 'datetime',
        ];
    }

    /** $data: id_akun (dari sesi, diisi FeedbackController), kategori, pesan (sudah di-trim). Status awal baru dan waktu kirim dari server. */
    public function simpan(array $data): HasilOperasi
    {
        $this->fill([
            'id_akun'     => $data['id_akun'],
            'kategori'    => $data['kategori'],
            'pesan'       => $data['pesan'],
            'waktu_kirim' => now(),
            'status'      => StatusFeedback::Baru,
        ]);

        try {
            $this->save(); // Q-003
        } catch (QueryException) {
            return HasilOperasi::gagal('SIMPAN_GAGAL', 'Feedback gagal disimpan'); // UC-09 Alternatif 1
        }

        return HasilOperasi::sukses(['id_feedback' => $this->id_feedback]);
    }

    /** Status hanya boleh maju, dan pembaruan saling menimpa dicegah syarat status lama. */
    public function perbaruiStatus(StatusFeedback $status): HasilOperasi
    {
        if (self::URUTAN[$status->value] <= self::URUTAN[$this->status->value]) {
            return HasilOperasi::gagal('STATUS_TIDAK_VALID', 'Status hanya dapat dimajukan');
        }

        $lama = $this->status;

        $baris = self::query() // Q-004
            ->where('id_feedback', $this->id_feedback)
            ->where('status', $lama->value)
            ->update(['status' => $status->value]);

        if ($baris === 0) {
            return HasilOperasi::gagal('STATUS_BERUBAH', 'Status feedback sudah diubah, muat ulang data');
        }

        $this->status = $status;
        $this->syncOriginalAttribute('status');

        return HasilOperasi::sukses(['id_feedback' => $this->id_feedback]);
    }
}