<?php

namespace App\Http\Controllers;

use App\Enums\StatusPublikasi;
use App\Http\Requests\SimpanMateriRequest;
use App\Models\KontenMateri;
use App\Models\MateriAksara;
use App\Support\Materi\DataKonten;
use App\Support\Materi\FilterMateri;
use App\Support\Materi\GagalOperasi;
use App\Support\Materi\HasilBerkas;
use App\Support\Materi\HasilOperasi;
use App\Support\Materi\HasilValidasi;
use App\Support\Materi\PaketMateri;
use App\Support\Materi\TargetLatihan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Contracts\View\View;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/** C17 MateriController (Controller). */
class MateriController extends Controller
{
    /* ===================== Operasi Tabel 4.1 ===================== */

    /** Algo-049 */
    public function ambilDaftarMateri(FilterMateri $filter): array
    {
        if ($filter->termasukDraf && ! $this->punyaHak('kelola_materi')) {
            $filter->termasukDraf = false; // Pelajar tidak boleh melihat draf
        }

        $progres = $this->petaProgres();
        $hasil = [];
        foreach (MateriAksara::ambilDaftarMateri($filter) as $materi) {
            $meta = $materi->ambilMetadata();
            $p = $progres[$materi->id_materi] ?? null;
            $meta->statusProgres = $p['status'] ?? ($progres === [] ? null : 'belum');
            $meta->persenProgres = $p['persen'] ?? ($progres === [] ? null : 0);
            $hasil[] = $meta;
        }

        return $hasil;
    }

    /** Algo-050 */
    public function muatPaketMateri(int $idMateri, string $versiAplikasi): PaketMateri
    {
        $materi = MateriAksara::find($idMateri);
        if ($materi === null || ($materi->status_publikasi !== StatusPublikasi::Terbit && ! ($this->punyaHak('kelola_materi') && $materi->status_publikasi === StatusPublikasi::Draf))) {
            return PaketMateri::galat('MATERI_TIDAK_TERSEDIA');
        }
        if ($materi->konten === null) {
            return PaketMateri::galat('KONTEN_TIDAK_ADA');
        }

        $paket = $materi->konten->ambilKonten($versiAplikasi);
        if ($paket->berhasil()) {
            $paket->statusProgres = $this->petaProgres()[$idMateri]['status'] ?? null;
        }

        return $paket;
    }

    /** Algo-051 */
    public function ambilAudioPengucapan(int $idMateri): HasilBerkas
    {
        $materi = MateriAksara::find($idMateri);
        if ($materi === null || $materi->status_publikasi !== StatusPublikasi::Terbit || $materi->konten === null) {
            return HasilBerkas::gagal('MATERI_TIDAK_TERSEDIA');
        }

        return $materi->konten->ambilAudio();
    }

    /** Dipanggil LatihanController (UC-03). $mode: menulis | bunyi | merangkai | transliterasi */
    public function ambilKontenLatihan(int $idMateri, string $mode): TargetLatihan
    {
        $materi = MateriAksara::find($idMateri);
        if ($materi === null || $materi->status_publikasi !== StatusPublikasi::Terbit) {
            return TargetLatihan::kosong($idMateri, $mode);
        }

        return $materi->ambilTargetLatihan($mode);
    }

    /** Algo-053 */
    public function tambahMateri(DataKonten $data, ?UploadedFile $berkas = null): HasilOperasi
    {
        if (! $this->punyaHak('kelola_materi')) {
            return HasilOperasi::gagal('AKSES_DITOLAK', 'Anda tidak berhak mengelola materi');
        }
        if ($berkas !== null) {
            $validasi = $this->validasiFormatFile($berkas);
            if (! $validasi->valid) {
                return HasilOperasi::gagal('FORMAT_TIDAK_VALID', $validasi->pesan);
            }
            if ($galat = $this->terapkanJson($data, $berkas)) {
                return $galat;
            }
        }

        $tersimpan = [];
        try {
            return DB::transaction(function () use ($data, $berkas, &$tersimpan) {
                $materi = new MateriAksara;
                $hasilMateri = $materi->simpan($data->metadata());
                if (! $hasilMateri->berhasil) {
                    throw new GagalOperasi($hasilMateri);
                }

                $data->pathAudio = $this->simpanBerkas($berkas, (int) $materi->id_materi, $tersimpan);
                $konten = new KontenMateri(['id_materi' => $materi->id_materi]);
                $hasilKonten = $konten->simpanKonten($data);
                if (! $hasilKonten->berhasil) {
                    throw new GagalOperasi($hasilKonten);
                }

                return HasilOperasi::sukses(['idMateri' => $materi->id_materi], 'Materi berhasil ditambahkan');
            });
        } catch (GagalOperasi $e) {
            $this->hapusBerkas($tersimpan);

            return $e->hasil;
        } catch (Throwable $e) {
            $this->hapusBerkas($tersimpan);
            Log::error('tambahMateri gagal', ['galat' => $e->getMessage()]);

            return HasilOperasi::gagal('SIMPAN_GAGAL', 'Materi gagal disimpan, coba lagi');
        }
    }

    /** Algo-054. Berkas baru (bila ada) dibawa di $data->berkasBaru. */
    public function perbaruiMateri(int $idMateri, DataKonten $data): HasilOperasi
    {
        if (! $this->punyaHak('kelola_materi')) {
            return HasilOperasi::gagal('AKSES_DITOLAK', 'Anda tidak berhak mengelola materi');
        }
        $materi = MateriAksara::find($idMateri);
        if ($materi === null || $materi->status_publikasi === StatusPublikasi::Arsip) {
            return HasilOperasi::gagal('TIDAK_DITEMUKAN', 'Materi tidak ditemukan');
        }
        if ($data->berkasBaru !== null) {
            $validasi = $this->validasiFormatFile($data->berkasBaru);
            if (! $validasi->valid) {
                return HasilOperasi::gagal('FORMAT_TIDAK_VALID', $validasi->pesan);
            }
            if ($galat = $this->terapkanJson($data, $data->berkasBaru)) {
                return $galat;
            }
        }

        $tersimpan = [];
        try {
            return DB::transaction(function () use ($materi, $data, &$tersimpan) {
                $hasil = $materi->perbaruiData($data->metadata($materi->id_materi));
                if (! $hasil->berhasil) {
                    throw new GagalOperasi($hasil);
                }

                $data->pathAudio = $this->simpanBerkas($data->berkasBaru, (int) $materi->id_materi, $tersimpan);
                $konten = $materi->konten;
                if ($konten === null) {
                    $konten = new KontenMateri(['id_materi' => $materi->id_materi]);
                    $hasil = $konten->simpanKonten($data);
                } else {
                    $hasil = $konten->perbaruiKonten($data);
                }
                if (! $hasil->berhasil) {
                    throw new GagalOperasi($hasil);
                }

                return HasilOperasi::sukses(['idMateri' => $materi->id_materi], 'Materi berhasil diperbarui');
            });
        } catch (GagalOperasi $e) {
            $this->hapusBerkas($tersimpan);

            return $e->hasil;
        } catch (Throwable $e) {
            $this->hapusBerkas($tersimpan);
            Log::error('perbaruiMateri gagal', ['galat' => $e->getMessage()]);

            return HasilOperasi::gagal('PERBARUI_GAGAL', 'Materi gagal diperbarui, coba lagi');
        }
    }

    /** Algo-052: hanya json, mp3, png, svg; maksimal 10 MB (config/ngaksara.php). */
    public function validasiFormatFile(UploadedFile $berkas): HasilValidasi
    {
        $ekstensi = strtolower($berkas->getClientOriginalExtension());
        if (! in_array($ekstensi, config('ngaksara.materi.ekstensi'), true)) {
            return HasilValidasi::tidakValid('Jenis berkas tidak didukung');
        }
        $maksKb = (int) config('ngaksara.materi.maks_kb');
        if ($berkas->getSize() > $maksKb * 1024) {
            return HasilValidasi::tidakValid('Ukuran berkas melebihi '.intdiv($maksKb, 1024).' MB');
        }
        if ($ekstensi === 'json') {
            $isi = json_decode((string) file_get_contents($berkas->getRealPath()), true);
            if (! is_array($isi)) {
                return HasilValidasi::tidakValid('Isi berkas json tidak valid');
            }
        }

        return HasilValidasi::valid();
    }

    /** Algo-055: penghapusan = pengarsipan; baris dan riwayat latihan tetap utuh. */
    public function hapusMateri(int $idMateri): HasilOperasi
    {
        if (! $this->punyaHak('kelola_materi')) {
            return HasilOperasi::gagal('AKSES_DITOLAK', 'Anda tidak berhak mengelola materi');
        }
        $materi = MateriAksara::find($idMateri);
        if ($materi === null || $materi->status_publikasi === StatusPublikasi::Arsip) {
            return HasilOperasi::gagal('TIDAK_DITEMUKAN', 'Materi tidak ditemukan');
        }
        $materi->status_publikasi = StatusPublikasi::Arsip;
        $materi->save();

        return HasilOperasi::sukses(['idMateri' => $idMateri], 'Materi diarsipkan');
    }

    /* ===================== Aksi HTTP (route -> operasi -> view) ===================== */

    /** I-04 Daftar Materi (Pelajar). Pilihan awal: Aksara Jawa, tingkat 1. */
    public function index(Request $request): View
    {
        $query = $request->query() + ['jenis' => 'jawa', 'tingkat' => 1];
        $filter = FilterMateri::dariQuery($query);

        return view('materi.index', [
            'daftar' => $this->ambilDaftarMateri($filter),
            'jenis' => $filter->jenisAksara,
            'tingkat' => $filter->tingkatKesulitan,
        ]);
    }

    /** I-05 Detail Materi. */
    public function show(int $materi): View
    {
        $paket = $this->muatPaketMateri($materi, (string) config('ngaksara.versi_aplikasi'));
        if ($paket->galat === 'MATERI_TIDAK_TERSEDIA') {
            abort(404);
        }

        return view('materi.show', ['paket' => $paket]);
    }

    public function audio(int $materi): Response
    {
        $hasil = $this->ambilAudioPengucapan($materi);
        if (! $hasil->berhasil) {
            abort(404, 'Audio pengucapan belum tersedia');
        }

        return Storage::disk(config('ngaksara.materi.disk'))->response($hasil->path);
    }

    /** I-18 Kelola Materi (Tim Materi). */
    public function kelola(Request $request): View
    {
        $this->wajibHak('kelola_materi');
        $filter = FilterMateri::dariQuery($request->query(), termasukDraf: true);

        return view('materi.kelola', [
            'daftar' => $this->ambilDaftarMateri($filter),
            'jenis' => $filter->jenisAksara,
        ]);
    }

    /** I-19 Unggah Materi (form kosong). */
    public function buat(): View
    {
        $this->wajibHak('kelola_materi');

        return view('materi.form', ['materi' => null]);
    }

    public function simpan(SimpanMateriRequest $request): RedirectResponse
    {
        $data = $request->keDataKonten();
        $hasil = $this->tambahMateri($data, $request->file('berkas'));

        return $this->balasan($hasil, 'materi.kelola', 'materi.buat');
    }

    /** Form sunting: I-19 dengan isian terisi. */
    public function sunting(int $materi): View
    {
        $this->wajibHak('kelola_materi');
        $model = MateriAksara::with('konten')->findOrFail($materi);
        abort_if($model->status_publikasi === StatusPublikasi::Arsip, 404);

        return view('materi.form', ['materi' => $model]);
    }

    public function perbarui(SimpanMateriRequest $request, int $materi): RedirectResponse
    {
        $model = MateriAksara::find($materi);
        $hasil = $this->perbaruiMateri($materi, $request->keDataKonten($model?->status_publikasi ?? StatusPublikasi::Draf));

        return $this->balasan($hasil, 'materi.kelola', 'materi.sunting', ['materi' => $materi]);
    }

    /** Transisi draf -> terbit lewat perbaruiMateri (tidak ada operasi baru di Tabel 4.1). */
    public function terbitkan(int $materi): RedirectResponse
    {
        $this->wajibHak('kelola_materi');
        $model = MateriAksara::with('konten')->findOrFail($materi);
        if ($model->konten === null) {
            return redirect()->route('materi.kelola')->with('galat', 'Materi belum punya konten, lengkapi dulu sebelum diterbitkan.');
        }

        $data = new DataKonten(
            namaMateri: $model->nama_materi,
            jenisAksara: $model->jenis_aksara,
            tingkatKesulitan: $model->tingkat_kesulitan,
            statusPublikasi: StatusPublikasi::Terbit,
            bentukAksara: $model->konten->bentuk_aksara,
            aturan: $model->konten->aturan,
            contoh: $model->konten->contoh,
            versiAplikasiMinimum: $model->konten->versi_aplikasi_minimum,
        );
        $hasil = $this->perbaruiMateri($materi, $data);
        if ($hasil->berhasil) {
            $hasil = HasilOperasi::sukses($hasil->data, 'Materi berhasil diterbitkan');
        }

        return $this->balasan($hasil, 'materi.kelola', 'materi.kelola');
    }

    public function arsipkan(int $materi): RedirectResponse
    {
        return $this->balasan($this->hapusMateri($materi), 'materi.kelola', 'materi.kelola');
    }

    /* ===================== Bantuan privat ===================== */

    /** Menggunakan AkunPengguna::verifikasiHakAkses (C01, Ryuza) bila sudah ada; sementara membaca kolom `peran`. */
    private function punyaHak(string $aksi): bool
    {
        $pengguna = Auth::user();
        if ($pengguna === null) {
            return false;
        }
        if (method_exists($pengguna, 'verifikasiHakAkses')) {
            return (bool) $pengguna->verifikasiHakAkses($aksi);
        }

        return $aksi === 'kelola_materi' ? ($pengguna->peran ?? null) === 'tim_materi' : true;
    }

    private function wajibHak(string $aksi): void
    {
        abort_unless($this->punyaHak($aksi), 403, 'Anda tidak berhak mengakses halaman ini');
    }

    /** idMateri => ['status' => 'selesai'|'sedang'|'belum', 'persen' => int] dari RiwayatLatihan::ambilStatusProgres (C03, Natan). */
    private function petaProgres(): array
    {
        $kelas = 'App\\Models\\RiwayatLatihan';
        $pengguna = Auth::user();
        if ($pengguna === null || ! class_exists($kelas) || ! method_exists($kelas, 'ambilStatusProgres')) {
            return [];
        }

        try {
            $peta = [];
            foreach ((array) $kelas::ambilStatusProgres($pengguna->getAuthIdentifier()) as $idMateri => $nilai) {
                $peta[(int) $idMateri] = is_array($nilai)
                    ? ['status' => $nilai['status'] ?? 'belum', 'persen' => (int) ($nilai['persen'] ?? 0)]
                    : ['status' => (string) $nilai, 'persen' => $nilai === 'selesai' ? 100 : 0];
            }

            return $peta;
        } catch (Throwable $e) {
            Log::warning('ambilStatusProgres gagal', ['galat' => $e->getMessage()]);

            return [];
        }
    }

    /** Berkas json boleh membawa bentukAksara/aturan/contoh dan menggantikan isian form. */
    private function terapkanJson(DataKonten $data, UploadedFile $berkas): ?HasilOperasi
    {
        if (strtolower($berkas->getClientOriginalExtension()) !== 'json') {
            return null;
        }
        $isi = json_decode((string) file_get_contents($berkas->getRealPath()), true);
        foreach (['bentukAksara', 'aturan', 'contoh'] as $kunci) {
            if (isset($isi[$kunci]) && is_string($isi[$kunci]) && trim($isi[$kunci]) !== '') {
                $data->{$kunci} = trim($isi[$kunci]);
            }
        }

        return null;
    }

    /** Menyimpan berkas ke disk materi. Mengembalikan path audio (mp3) atau null; path yang ditulis dicatat di $tersimpan supaya bisa dibersihkan bila transaksi dibatalkan. */
    private function simpanBerkas(?UploadedFile $berkas, int $idMateri, array &$tersimpan): ?string
    {
        if ($berkas === null) {
            return null;
        }
        $ekstensi = strtolower($berkas->getClientOriginalExtension());
        if ($ekstensi === 'json') {
            return null; // isi json sudah diterapkan ke DataKonten, berkasnya tidak perlu disimpan
        }

        $folder = config('ngaksara.materi.folder').'/'.$idMateri;
        $nama = ($ekstensi === 'mp3' ? 'audio' : 'gambar-'.Str::random(6)).'.'.$ekstensi;
        $path = Storage::disk(config('ngaksara.materi.disk'))->putFileAs($folder, $berkas, $nama);
        $tersimpan[] = $path;

        return $ekstensi === 'mp3' ? $path : null;
    }

    private function hapusBerkas(array $path): void
    {
        foreach ($path as $p) {
            Storage::disk(config('ngaksara.materi.disk'))->delete($p);
        }
    }

    private function balasan(HasilOperasi $hasil, string $rutSukses, string $rutGagal, array $param = []): RedirectResponse
    {
        if ($hasil->berhasil) {
            return redirect()->route($rutSukses)->with('status', $hasil->pesan);
        }

        return redirect()->route($rutGagal, $param)->withInput()->with('galat', $hasil->pesan);
    }
}
