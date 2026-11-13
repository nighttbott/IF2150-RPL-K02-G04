<?php

namespace App\Models;

use App\Enums\StatusKelas;
use App\Support\Progres\HasilOperasi;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\UniqueConstraintViolationException;
use RuntimeException;
use Throwable;

/** C23 KelasBelajar (tabel kelas_belajar). */
class KelasBelajar extends Model
{
    protected $table = 'kelas_belajar';

    protected $primaryKey = 'id_kelas';

    public $timestamps = false; // tabel kelas_belajar tidak memiliki created_at/updated_at

    protected $fillable = ['nama', 'pemilik_id', 'kode_bergabung', 'status', 'status_scoreboard'];

    // Tanpa I, O, 0, 1 agar tidak ambigu saat dibaca.
    private const ALFABET = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

    protected function casts(): array
    {
        return ['status_scoreboard' => 'boolean'];
    }

    /** Algo-087 */
    public function simpan(array $data): HasilOperasi
    {
        $nama = trim((string) ($data['nama'] ?? ''));
        if ($nama === '' || mb_strlen($nama) > 100) {
            return HasilOperasi::gagal('NAMA_TIDAK_VALID', 'Nama kelas wajib diisi (maks. 100 karakter).');
        }
        if (empty($data['pemilik_id'])) {
            return HasilOperasi::gagal('PEMILIK_TIDAK_VALID', 'Pemilik kelas tidak diketahui.');
        }

        for ($percobaan = 1; $percobaan <= 3; $percobaan++) {
            try {
                $this->fill([
                    'nama' => $nama,
                    'pemilik_id' => $data['pemilik_id'],
                    'kode_bergabung' => $this->buatKodeBergabung(),
                    'status' => StatusKelas::Aktif->value,
                    'status_scoreboard' => false,
                ])->save();

                return HasilOperasi::sukses([
                    'idKelas' => $this->id_kelas,
                    'kodeBergabung' => $this->kode_bergabung,
                ]);
            } catch (UniqueConstraintViolationException) {
                continue; // kode yang sama terpakai bersamaan, ulangi dengan kode baru
            } catch (Throwable) {
                return HasilOperasi::gagal('GAGAL_SIMPAN', 'Kelas gagal disimpan, coba lagi.');
            }
        }

        return HasilOperasi::gagal('GAGAL_SIMPAN', 'Kelas gagal disimpan, coba lagi.');
    }

    /** Algo-088 */
    public function buatKodeBergabung(): string
    {
        $batas = strlen(self::ALFABET) - 1;

        for ($percobaan = 1; $percobaan <= 10; $percobaan++) {
            $kode = '';
            for ($i = 0; $i < 6; $i++) {
                $kode .= self::ALFABET[random_int(0, $batas)];
            }
            if (! static::where('kode_bergabung', $kode)->exists()) {
                return $kode;
            }
        }

        throw new RuntimeException('KODE_TIDAK_TERSEDIA');
    }

    /** Algo-089: kode lama langsung tidak berlaku; anggota lama tidak terpengaruh. */
    public function gantiKodeBergabung(int $idPengajar): HasilOperasi
    {
        if (! $this->periksaPemilik($idPengajar)) {
            return HasilOperasi::gagal('AKSES_DITOLAK', 'Anda bukan pemilik kelas ini.');
        }

        for ($percobaan = 1; $percobaan <= 3; $percobaan++) {
            try {
                $kodeBaru = $this->buatKodeBergabung();
                $baris = static::whereKey($this->getKey())
                    ->where('pemilik_id', $idPengajar)
                    ->where('status', StatusKelas::Aktif->value)
                    ->update(['kode_bergabung' => $kodeBaru]);

                if ($baris === 0) {
                    return HasilOperasi::gagal('TIDAK_DITEMUKAN', 'Kelas tidak ditemukan atau tidak aktif.');
                }

                $this->setAttribute('kode_bergabung', $kodeBaru);
                $this->syncOriginalAttribute('kode_bergabung');

                return HasilOperasi::sukses(['kodeBergabung' => $kodeBaru]);
            } catch (UniqueConstraintViolationException) {
                continue;
            } catch (Throwable) {
                return HasilOperasi::gagal('GAGAL_SIMPAN', 'Kode gagal diganti, coba lagi.');
            }
        }

        return HasilOperasi::gagal('GAGAL_SIMPAN', 'Kode gagal diganti, coba lagi.');
    }

    /** Algo-090 */
    public function periksaPemilik(?int $idPengajar): bool
    {
        return $idPengajar !== null && (int) $this->pemilik_id === $idPengajar;
    }

    /** Algo-091 */
    public function aturScoreboard(bool $aktif): HasilOperasi
    {
        try {
            $this->status_scoreboard = $aktif;
            $this->save();
        } catch (Throwable) {
            return HasilOperasi::gagal('GAGAL_SIMPAN', 'Pengaturan papan peringkat gagal disimpan.');
        }

        return HasilOperasi::sukses(['statusScoreboard' => $aktif ? 'aktif' : 'nonaktif']);
    }

    /** Algo-092. Static agar mudah dipanggil controller. */
    public static function daftarKelasPengajar(int $idPengajar): Collection
    {
        return static::where('pemilik_id', $idPengajar)
            ->where('status', StatusKelas::Aktif->value)
            ->orderBy('nama')
            ->get();
    }
}
