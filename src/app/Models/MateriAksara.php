<?php

namespace App\Models;

use App\Enums\JenisAksara;
use App\Enums\StatusPublikasi;
use App\Support\Materi\FilterMateri;
use App\Support\Materi\HasilOperasi;
use App\Support\Materi\MetadataMateri;
use App\Support\Materi\PengolahKonten;
use App\Support\Materi\TargetLatihan;
use App\Support\Materi\TemplateAksara;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

/** C02 MateriAksara (Model). */
class MateriAksara extends Model
{
    protected $table = 'materi_aksara';

    protected $primaryKey = 'id_materi';

    protected $fillable = ['nama_materi', 'jenis_aksara', 'tingkat_kesulitan', 'status_publikasi'];

    protected function casts(): array
    {
        return [
            'jenis_aksara' => JenisAksara::class,
            'status_publikasi' => StatusPublikasi::class,
            'tingkat_kesulitan' => 'integer',
        ];
    }

    public function konten(): HasOne
    {
        return $this->hasOne(KontenMateri::class, 'id_materi', 'id_materi');
    }

    public function ambilMetadata(): MetadataMateri
    {
        return new MetadataMateri(
            $this->id_materi,
            $this->nama_materi,
            $this->jenis_aksara,
            $this->tingkat_kesulitan,
            $this->status_publikasi,
            versiKonten: $this->konten?->versi_konten,
        );
    }

    /** Algo-042: bahan latihan dari konten materi menurut mode (menulis, bunyi, merangkai). */
    public function ambilTargetLatihan(string $mode): TargetLatihan
    {
        $konten = $this->konten;
        if ($konten === null) {
            return TargetLatihan::kosong((int) $this->id_materi, $mode);
        }

        $butir = [];
        if ($mode === 'merangkai') {
            $butir = PengolahKonten::pasangContoh($konten->contoh);
        } else { // menulis, bunyi
            foreach (PengolahKonten::pecahBentuk($konten->bentuk_aksara) as $karakter) {
                $templat = TemplateAksara::cari($this->jenis_aksara, $karakter);
                $butir[] = [
                    'karakter' => $karakter,
                    'bunyi' => $templat['kode'] ?? null,
                    'template' => $templat['goresan'] ?? null,
                    'tengah' => $templat['tengah'] ?? null,
                ];
            }
        }

        return new TargetLatihan((int) $this->id_materi, $mode, $butir);
    }

    /** Algo-044: memperbarui nama, jenis, tingkat, dan status publikasi. */
    public function perbaruiData(MetadataMateri $data): HasilOperasi
    {
        if ($galat = self::validasi($data)) {
            return $galat;
        }
        if (self::adaDuplikat($data, $this->id_materi)) {
            return HasilOperasi::gagal('DUPLIKAT', 'Materi dengan nama, jenis, dan tingkat sama sudah ada');
        }
        if (! $this->exists) {
            return HasilOperasi::gagal('TIDAK_DITEMUKAN', 'Materi tidak ditemukan');
        }

        $this->nama_materi = trim($data->namaMateri);
        $this->jenis_aksara = $data->jenisAksara;
        $this->tingkat_kesulitan = $data->tingkatKesulitan;
        $this->status_publikasi = $data->statusPublikasi;
        $this->save();

        return HasilOperasi::sukses(['idMateri' => $this->id_materi]);
    }

    /** Algo-043: menyimpan metadata materi baru; idMateri baru ada di data hasil. */
    public function simpan(MetadataMateri $data): HasilOperasi
    {
        if ($galat = self::validasi($data)) {
            return $galat;
        }
        if (self::adaDuplikat($data, null)) {
            return HasilOperasi::gagal('DUPLIKAT', 'Materi dengan nama, jenis, dan tingkat sama sudah ada');
        }

        $this->fill([
            'nama_materi' => trim($data->namaMateri),
            'jenis_aksara' => $data->jenisAksara,
            'tingkat_kesulitan' => $data->tingkatKesulitan,
            'status_publikasi' => $data->statusPublikasi,
        ])->save();

        return HasilOperasi::sukses(['idMateri' => $this->id_materi]);
    }

    /** Algo-040: materi sesuai filter. Arsip tidak pernah ikut; draf hanya bila filter->termasukDraf. @return list<MateriAksara> */
    public static function ambilDaftarMateri(FilterMateri $filter): array
    {
        $query = static::query()->with('konten')->where('status_publikasi', '!=', StatusPublikasi::Arsip->value);
        if (! $filter->termasukDraf) {
            $query->where('status_publikasi', StatusPublikasi::Terbit->value);
        }
        if ($filter->jenisAksara !== null) {
            $query->where('jenis_aksara', $filter->jenisAksara->value);
        }
        if ($filter->tingkatKesulitan !== null) {
            $query->where('tingkat_kesulitan', $filter->tingkatKesulitan);
        }

        return $query->orderBy('jenis_aksara')->orderBy('tingkat_kesulitan')->orderBy('nama_materi')->get()->all();
    }

    /** Algo-041: materi terbit yang belum dikuasai. $penguasaan: idMateri => skor 0..100. @param  array<int, int|float|null>  $penguasaan @return list<MateriAksara> */
    public static function ambilMateriRekomendasi(array $penguasaan): array
    {
        $semua = static::ambilDaftarMateri(new FilterMateri);

        $kandidat = [];
        foreach ($semua as $m) {
            if (! static::sudahTuntas($m, $penguasaan) && static::tingkatTerbuka($m, $semua, $penguasaan)) {
                $kandidat[] = $m;
            }
        }

        usort($kandidat, fn ($a, $b) => [$a->tingkat_kesulitan, $penguasaan[$a->id_materi] ?? 0] <=> [$b->tingkat_kesulitan, $penguasaan[$b->id_materi] ?? 0]);

        return array_slice($kandidat, 0, (int) config('ngaksara.materi.maks_rekomendasi', 3));
    }

    /** Algo-041: materi tuntas bila penguasaannya mencapai ambang. */
    public static function sudahTuntas(MateriAksara $m, array $penguasaan): bool
    {
        $nilai = $penguasaan[$m->id_materi] ?? null;

        return $nilai !== null && $nilai >= config('ngaksara.materi.ambang_tuntas', 70);
    }

    /** Algo-041: tingkat terbuka bila semua materi tingkat di bawahnya (jenis sama) sudah tuntas. */
    public static function tingkatTerbuka(MateriAksara $m, iterable $semua, array $penguasaan): bool
    {
        foreach ($semua as $lain) {
            if ($lain->jenis_aksara === $m->jenis_aksara
                && $lain->tingkat_kesulitan < $m->tingkat_kesulitan
                && ! static::sudahTuntas($lain, $penguasaan)) {
                return false;
            }
        }

        return true;
    }
    private static function validasi(MetadataMateri $data): ?HasilOperasi
    {
        $nama = trim($data->namaMateri);
        if ($nama === '' || mb_strlen($nama) > 100) {
            return HasilOperasi::gagal('NAMA_TIDAK_VALID', 'Nama materi wajib diisi (maks. 100 karakter)');
        }
        if ($data->tingkatKesulitan < 1 || $data->tingkatKesulitan > 3) {
            return HasilOperasi::gagal('DATA_TIDAK_VALID', 'Tingkat kesulitan harus 1 sampai 3');
        }

        return null;
    }

    private static function adaDuplikat(MetadataMateri $data, ?int $kecualiId): bool
    {
        $query = static::query()
            ->where('nama_materi', trim($data->namaMateri))
            ->where('jenis_aksara', $data->jenisAksara->value)
            ->where('tingkat_kesulitan', $data->tingkatKesulitan)
            ->where('status_publikasi', '!=', StatusPublikasi::Arsip->value);
        if ($kecualiId !== null) {
            $query->where('id_materi', '!=', $kecualiId);
        }

        return $query->exists();
    }
}
