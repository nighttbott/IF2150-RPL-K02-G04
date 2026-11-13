<?php

namespace App\Models;

use App\Support\Materi\DataKonten;
use App\Support\Materi\HasilBerkas;
use App\Support\Materi\HasilOperasi;
use App\Support\Materi\PaketMateri;
use App\Support\Materi\PengolahKonten;
use App\Support\Materi\TemplateAksara;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/** C21 KontenMateri (Model). Atribut Tabel 4.1 -> kolom id_konten, id_materi, bentuk_aksara, aturan, contoh, path_audio, versi_konten, versi_aplikasi_minimum. */
class KontenMateri extends Model
{
    protected $table = 'konten_materi';

    protected $primaryKey = 'id_konten';

    protected $fillable = ['id_materi', 'bentuk_aksara', 'aturan', 'contoh', 'path_audio', 'versi_konten', 'versi_aplikasi_minimum'];

    public function materi(): BelongsTo
    {
        return $this->belongsTo(MateriAksara::class, 'id_materi', 'id_materi');
    }

    /** Algo-056: konten terbaru (terkini atau arsip) yang kompatibel dengan versi aplikasi. */
    public function ambilKonten(string $versiAplikasi): PaketMateri
    {
        $kandidat = [$this->only(['bentuk_aksara', 'aturan', 'contoh', 'path_audio', 'versi_konten', 'versi_aplikasi_minimum'])];
        $arsip = DB::table('konten_materi_arsip')
            ->where('id_materi', $this->id_materi)
            ->get(['bentuk_aksara', 'aturan', 'contoh', 'path_audio', 'versi_konten', 'versi_aplikasi_minimum']);
        foreach ($arsip as $baris) {
            $kandidat[] = (array) $baris;
        }

        $pilihan = PengolahKonten::pilihVersi($kandidat, $versiAplikasi);
        if ($pilihan === null) {
            return PaketMateri::galat('KONTEN_TIDAK_KOMPATIBEL');
        }

        $materi = $this->materi;
        $aksara = [];
        foreach (PengolahKonten::pecahBentuk($pilihan['bentuk_aksara']) as $karakter) {
            $aksara[] = ['karakter' => $karakter, 'kode' => $materi ? (TemplateAksara::cari($materi->jenis_aksara, $karakter)['kode'] ?? null) : null];
        }

        return new PaketMateri(
            idMateri: (int) $this->id_materi,
            namaMateri: $materi?->nama_materi ?? '',
            jenisAksara: $materi?->jenis_aksara->value ?? '',
            tingkatKesulitan: (int) ($materi?->tingkat_kesulitan ?? 0),
            aksara: $aksara,
            aturan: $pilihan['aturan'],
            contoh: $pilihan['contoh'],
            pathAudio: $pilihan['path_audio'],
            versiKonten: $pilihan['versi_konten'],
        );
    }

    /** Lokasi berkas audio pengucapan; isi berkas dikirim oleh controller. */
    public function ambilAudio(): HasilBerkas
    {
        if ($this->path_audio === null || $this->path_audio === '') {
            return HasilBerkas::gagal('AUDIO_TIDAK_ADA');
        }
        if (! Storage::disk(config('ngaksara.materi.disk'))->exists($this->path_audio)) {
            return HasilBerkas::gagal('BERKAS_HILANG');
        }

        return HasilBerkas::sukses($this->path_audio);
    }

    /** Algo-058: arsipkan versi lama, lalu timpa dengan versi baru (versi minor naik). */
    public function perbaruiKonten(DataKonten $data): HasilOperasi
    {
        if (! $data->kontenLengkap()) {
            return HasilOperasi::gagal('KONTEN_TIDAK_LENGKAP', 'Konten belum lengkap: tulis daftar aksara setelah tanda ":" pada nama (mis. "Nglegena: ha na ca"), isi bagian "Contoh:", atau unggah konten.json');
        }

        $pathAudio = $data->pathAudio ?? $this->path_audio; // tanpa audio baru, audio lama dipertahankan
        $sama = $this->bentuk_aksara === trim($data->bentukAksara)
            && $this->aturan === trim($data->aturan)
            && $this->contoh === trim($data->contoh)
            && $this->path_audio === $pathAudio;
        if ($sama) {
            return HasilOperasi::sukses(['idKonten' => $this->id_konten], 'Tidak ada perubahan');
        }

        $arsip = $this->arsipkanVersi();
        if (! $arsip->berhasil) {
            return $arsip; // jangan menimpa tanpa arsip
        }

        $this->fill([
            'bentuk_aksara' => trim($data->bentukAksara),
            'aturan' => trim($data->aturan),
            'contoh' => trim($data->contoh),
            'path_audio' => $pathAudio,
            'versi_konten' => PengolahKonten::naikkanMinor($this->versi_konten),
            'versi_aplikasi_minimum' => $data->versiAplikasiMinimum,
        ])->save();

        return HasilOperasi::sukses(['idKonten' => $this->id_konten]);
    }

    /** Algo-059: salin konten saat ini ke tabel arsip. */
    public function arsipkanVersi(): HasilOperasi
    {
        $ok = DB::table('konten_materi_arsip')->insert([
            'id_konten' => $this->id_konten,
            'id_materi' => $this->id_materi,
            'bentuk_aksara' => $this->bentuk_aksara,
            'aturan' => $this->aturan,
            'contoh' => $this->contoh,
            'path_audio' => $this->path_audio,
            'versi_konten' => $this->versi_konten,
            'versi_aplikasi_minimum' => $this->versi_aplikasi_minimum,
            'diarsipkan_pada' => now(),
        ]);

        return $ok ? HasilOperasi::sukses(['idKonten' => $this->id_konten]) : HasilOperasi::gagal('ARSIP_GAGAL', 'Versi konten gagal diarsipkan');
    }

    /** Algo-057: konten pertama sebuah materi disimpan sebagai versi 1.0 (id_materi diisi pemanggil). */
    public function simpanKonten(DataKonten $data): HasilOperasi
    {
        if (! $data->kontenLengkap()) {
            return HasilOperasi::gagal('KONTEN_TIDAK_LENGKAP', 'Konten belum lengkap: tulis daftar aksara setelah tanda ":" pada nama (mis. "Nglegena: ha na ca"), isi bagian "Contoh:", atau unggah konten.json');
        }

        $this->fill([
            'bentuk_aksara' => trim($data->bentukAksara),
            'aturan' => trim($data->aturan),
            'contoh' => trim($data->contoh),
            'path_audio' => $data->pathAudio,
            'versi_konten' => '1.0',
            'versi_aplikasi_minimum' => $data->versiAplikasiMinimum,
        ])->save();

        return HasilOperasi::sukses(['idKonten' => $this->id_konten]);
    }
}
