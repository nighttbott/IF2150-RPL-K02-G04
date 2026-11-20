<?php

namespace Tests\Unit\Hiupl;

use App\Enums\JenisAksara;
use App\Enums\StatusPublikasi;
use App\Http\Controllers\MateriController;
use App\Models\AkunPengguna;
use App\Models\MateriAksara;
use App\Support\Materi\FilterMateri;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\Feature\BantuanData;
use Tests\TestCase;

/**
 * HIUPL 4.2: pengujian unit kelas C17 MateriController (tingkat Mendalam: hak akses Tim Materi, validasi berkas, transaksi simpan).
 */
class C17MateriControllerTest extends TestCase
{
    use BantuanData, BantuanMateri, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    private function sebagai(string $peran): MateriController
    {
        $this->actingAs(AkunPengguna::factory()->create(['peran' => $peran]));

        return app(MateriController::class);
    }

    /** @uji C17-UT01 | Normal | ambilDaftarMateri(filter: FilterMateri): List<MetadataMateri> | Pelajar dengan riwayat skor 90 pada materi terbit | Daftar berisi materi dengan statusProgres selesai, persen 90 */
    #[TestDox('C17-UT01 Normal: daftar materi beserta status progres Pelajar')]
    public function test_ut01_daftar(): void
    {
        $m = $this->materiLengkap();
        $p = $this->buatPelajar();
        $this->riwayat($p, $m, 90);
        $this->actingAs($p);
        $d = app(MateriController::class)->ambilDaftarMateri(new FilterMateri(JenisAksara::Jawa, 1));

        $this->assertSame(['selesai', 90], [$d[0]->statusProgres, $d[0]->persenProgres]);
    }

    /** @uji C17-UT02 | Batas | ambilDaftarMateri(filter: FilterMateri): List<MetadataMateri> | Pelajar meminta termasukDraf = true | Permintaan draf diabaikan; hanya materi terbit */
    #[TestDox('C17-UT02 Batas: Pelajar tidak dapat memaksa melihat draf')]
    public function test_ut02_pelajar_minta_draf(): void
    {
        $this->materiLengkap('Terbit');
        $this->materiLengkap('Draf', 1, StatusPublikasi::Draf);
        $d = $this->sebagai('pelajar')->ambilDaftarMateri(new FilterMateri(null, null, true));

        $this->assertSame(['Terbit'], array_map(fn ($x) => $x->namaMateri, $d));
    }

    /** @uji C17-UT03 | Negatif | ambilDaftarMateri(filter: FilterMateri): List<MetadataMateri> | Filter tingkat 3 tanpa materi | Daftar kosong */
    #[TestDox('C17-UT03 Negatif: filter tanpa hasil')]
    public function test_ut03_daftar_kosong(): void
    {
        $this->materiLengkap();

        $this->assertSame([], $this->sebagai('pelajar')->ambilDaftarMateri(new FilterMateri(null, 3)));
    }

    /** @uji C17-UT04 | Normal | muatPaketMateri(idMateri: Integer, versiAplikasi: String): PaketMateri | Materi terbit, versi 1.0 | Paket berhasil dengan aksara ꦲ ꦤ */
    #[TestDox('C17-UT04 Normal: paket materi terbit dimuat')]
    public function test_ut04_paket(): void
    {
        $p = $this->sebagai('pelajar')->muatPaketMateri($this->materiLengkap()->id_materi, '1.0');

        $this->assertTrue($p->berhasil());
        $this->assertCount(2, $p->aksara);
    }

    /** @uji C17-UT05 | Batas | muatPaketMateri(idMateri: Integer, versiAplikasi: String): PaketMateri | Materi draf dibuka Tim Materi (pratinjau) dan Pelajar | Tim Materi: berhasil; Pelajar: MATERI_TIDAK_TERSEDIA */
    #[TestDox('C17-UT05 Batas: draf hanya dapat dipratinjau Tim Materi')]
    public function test_ut05_paket_draf(): void
    {
        $id = $this->materiLengkap('Draf', 1, StatusPublikasi::Draf)->id_materi;

        $this->assertTrue($this->sebagai('tim_materi')->muatPaketMateri($id, '1.0')->berhasil());
        $this->assertSame('MATERI_TIDAK_TERSEDIA', $this->sebagai('pelajar')->muatPaketMateri($id, '1.0')->galat);
    }

    /** @uji C17-UT06 | Negatif | muatPaketMateri(idMateri: Integer, versiAplikasi: String): PaketMateri | ID tidak ada; materi terbit tanpa konten | MATERI_TIDAK_TERSEDIA; KONTEN_TIDAK_ADA */
    #[TestDox('C17-UT06 Negatif: materi tidak ada atau tanpa konten')]
    public function test_ut06_paket_gagal(): void
    {
        $k = $this->sebagai('pelajar');

        $this->assertSame('MATERI_TIDAK_TERSEDIA', $k->muatPaketMateri(999999, '1.0')->galat);
        $this->assertSame('KONTEN_TIDAK_ADA', $k->muatPaketMateri($this->buatMateri('Tanpa Konten')->id_materi, '1.0')->galat);
    }

    /** @uji C17-UT07 | Normal | ambilAudioPengucapan(idMateri: Integer): HasilBerkas | Materi terbit dengan berkas audio ada | Berhasil dengan path audio */
    #[TestDox('C17-UT07 Normal: audio pengucapan tersedia')]
    public function test_ut07_audio(): void
    {
        Storage::disk('local')->put('materi/ha.mp3', 'ID3');

        $this->assertTrue($this->sebagai('pelajar')->ambilAudioPengucapan($this->materiLengkap('A', 1, StatusPublikasi::Terbit, 'materi/ha.mp3')->id_materi)->berhasil);
    }

    /** @uji C17-UT08 | Batas | ambilAudioPengucapan(idMateri: Integer): HasilBerkas | Materi terbit tanpa audio | AUDIO_TIDAK_ADA (halaman tetap tampil tanpa tombol audio) */
    #[TestDox('C17-UT08 Batas: materi tanpa audio')]
    public function test_ut08_audio_tidak_ada(): void
    {
        $this->assertSame('AUDIO_TIDAK_ADA', $this->sebagai('pelajar')->ambilAudioPengucapan($this->materiLengkap()->id_materi)->kode);
    }

    /** @uji C17-UT09 | Negatif | ambilAudioPengucapan(idMateri: Integer): HasilBerkas | Materi draf; audio terdaftar tetapi berkas hilang | MATERI_TIDAK_TERSEDIA; BERKAS_HILANG (UC02 Alternatif 2) */
    #[TestDox('C17-UT09 Negatif: audio materi draf atau berkas hilang')]
    public function test_ut09_audio_gagal(): void
    {
        $k = $this->sebagai('pelajar');

        $this->assertSame('MATERI_TIDAK_TERSEDIA', $k->ambilAudioPengucapan($this->materiLengkap('Draf', 1, StatusPublikasi::Draf)->id_materi)->kode);
        $this->assertSame('BERKAS_HILANG', $k->ambilAudioPengucapan($this->materiLengkap('Hilang', 1, StatusPublikasi::Terbit, 'materi/x.mp3')->id_materi)->kode);
    }

    /** @uji C17-UT10 | Normal | tambahMateri(data: DataKonten, berkas: Berkas?): HasilOperasi | Tim Materi menambah materi dengan audio mp3 | Berhasil; materi + konten tersimpan; berkas audio ada di penyimpanan */
    #[TestDox('C17-UT10 Normal: Tim Materi menambah materi dengan audio')]
    public function test_ut10_tambah(): void
    {
        $h = $this->sebagai('tim_materi')->tambahMateri($this->dataKonten('Baru', 1, StatusPublikasi::Draf), UploadedFile::fake()->create('ha.mp3', 50, 'audio/mpeg'));
        $m = MateriAksara::find($h->data['idMateri']);

        $this->assertTrue($h->berhasil);
        Storage::disk('local')->assertExists($m->konten->path_audio);
    }

    /** @uji C17-UT11 | Batas | tambahMateri(data: DataKonten, berkas: Berkas?): HasilOperasi | Tanpa berkas (berkas opsional) | Berhasil; path_audio null */
    #[TestDox('C17-UT11 Batas: materi tanpa berkas tetap dapat ditambahkan')]
    public function test_ut11_tambah_tanpa_berkas(): void
    {
        $h = $this->sebagai('tim_materi')->tambahMateri($this->dataKonten('Tanpa Berkas'));

        $this->assertNull(MateriAksara::find($h->data['idMateri'])->konten->path_audio);
    }

    /** @uji C17-UT12 | Negatif | tambahMateri(data: DataKonten, berkas: Berkas?): HasilOperasi | (a) Pelajar menambah materi; (b) berkas .exe; (c) konten tidak lengkap | AKSES_DITOLAK; FORMAT_TIDAK_VALID; KONTEN_TIDAK_LENGKAP dan transaksi dibatalkan (tidak ada materi) */
    #[TestDox('C17-UT12 Negatif: akses, format, dan konten tidak valid ditolak')]
    public function test_ut12_tambah_ditolak(): void
    {
        $this->assertSame('AKSES_DITOLAK', $this->sebagai('pelajar')->tambahMateri($this->dataKonten('X'))->kode);
        $tim = $this->sebagai('tim_materi');
        $this->assertSame('FORMAT_TIDAK_VALID', $tim->tambahMateri($this->dataKonten('X'), UploadedFile::fake()->create('virus.exe', 5))->kode);
        $kosong = $this->dataKonten('X');
        $kosong->aturan = '';
        $this->assertSame('KONTEN_TIDAK_LENGKAP', $tim->tambahMateri($kosong)->kode);
        $this->assertSame(0, MateriAksara::count());
    }

    /** @uji C17-UT13 | Normal | validasiFormatFile(berkas: Berkas): HasilValidasi | Berkas json berisi objek valid dan png kecil | Keduanya valid */
    #[TestDox('C17-UT13 Normal: berkas json dan png valid')]
    public function test_ut13_format_valid(): void
    {
        $k = $this->sebagai('tim_materi');

        $this->assertTrue($k->validasiFormatFile(UploadedFile::fake()->createWithContent('m.json', '{"aturan":"a"}'))->valid);
        $this->assertTrue($k->validasiFormatFile(UploadedFile::fake()->image('a.png'))->valid);
    }

    /** @uji C17-UT14 | Batas | validasiFormatFile(berkas: Berkas): HasilValidasi | mp3 tepat 10.240 KB (10 MB) dan 10.241 KB; ekstensi "MP3" huruf besar | 10 MB valid; 10.241 KB tidak valid; MP3 valid */
    #[TestDox('C17-UT14 Batas: ukuran maksimum 10 MB')]
    public function test_ut14_format_batas(): void
    {
        $k = $this->sebagai('tim_materi');

        $this->assertTrue($k->validasiFormatFile(UploadedFile::fake()->create('a.mp3', 10240))->valid);
        $this->assertFalse($k->validasiFormatFile(UploadedFile::fake()->create('a.mp3', 10241))->valid);
        $this->assertTrue($k->validasiFormatFile(UploadedFile::fake()->create('A.MP3', 10))->valid);
    }

    /** @uji C17-UT15 | Negatif | validasiFormatFile(berkas: Berkas): HasilValidasi | Berkas .pdf dan berkas json yang isinya bukan JSON | Tidak valid: "Jenis berkas tidak didukung" dan "Isi berkas json tidak valid" (UC06 Alternatif 1) */
    #[TestDox('C17-UT15 Negatif: jenis berkas tidak didukung dan json rusak')]
    public function test_ut15_format_tidak_valid(): void
    {
        $k = $this->sebagai('tim_materi');

        $this->assertSame('Jenis berkas tidak didukung', $k->validasiFormatFile(UploadedFile::fake()->create('a.pdf', 10))->pesan);
        $this->assertSame('Isi berkas json tidak valid', $k->validasiFormatFile(UploadedFile::fake()->createWithContent('a.json', 'bukan json'))->pesan);
    }

    /** @uji C17-UT16 | Normal | perbaruiMateri(idMateri: Integer, data: DataKonten): HasilOperasi | Tim Materi mengubah aturan materi | Berhasil; versi konten naik 1.1 */
    #[TestDox('C17-UT16 Normal: materi diperbarui dan versi naik')]
    public function test_ut16_perbarui(): void
    {
        $m = $this->materiLengkap();
        $data = $this->dataKonten();
        $data->aturan = 'Aturan baru.';

        $this->assertTrue($this->sebagai('tim_materi')->perbaruiMateri($m->id_materi, $data)->berhasil);
        $this->assertSame('1.1', $m->fresh()->konten->versi_konten);
    }

    /** @uji C17-UT17 | Negatif | perbaruiMateri(idMateri: Integer, data: DataKonten): HasilOperasi | Materi yang sudah diarsipkan; Pengajar memperbarui | TIDAK_DITEMUKAN; AKSES_DITOLAK */
    #[TestDox('C17-UT17 Negatif: materi arsip dan peran tanpa hak ditolak')]
    public function test_ut17_perbarui_ditolak(): void
    {
        $arsip = $this->materiLengkap('Arsip', 1, StatusPublikasi::Arsip);

        $this->assertSame('TIDAK_DITEMUKAN', $this->sebagai('tim_materi')->perbaruiMateri($arsip->id_materi, $this->dataKonten('Arsip'))->kode);
        $this->assertSame('AKSES_DITOLAK', $this->sebagai('pengajar')->perbaruiMateri($arsip->id_materi, $this->dataKonten('Arsip'))->kode);
    }

    /** @uji C17-UT18 | Normal | hapusMateri(idMateri: Integer): HasilOperasi | Tim Materi menghapus materi terbit | Status menjadi arsip (soft delete); materi tidak tampil di daftar Pelajar */
    #[TestDox('C17-UT18 Normal: hapus materi mengarsipkan')]
    public function test_ut18_hapus(): void
    {
        $m = $this->materiLengkap();

        $this->assertTrue($this->sebagai('tim_materi')->hapusMateri($m->id_materi)->berhasil);
        $this->assertSame('arsip', $m->fresh()->status_publikasi->value);
    }

    /** @uji C17-UT19 | Batas | hapusMateri(idMateri: Integer): HasilOperasi | Menghapus materi yang sudah diarsipkan (dua kali) | TIDAK_DITEMUKAN pada percobaan kedua */
    #[TestDox('C17-UT19 Batas: hapus dua kali')]
    public function test_ut19_hapus_dua_kali(): void
    {
        $m = $this->materiLengkap();
        $k = $this->sebagai('tim_materi');
        $k->hapusMateri($m->id_materi);

        $this->assertSame('TIDAK_DITEMUKAN', $k->hapusMateri($m->id_materi)->kode);
    }

    /** @uji C17-UT20 | Negatif | hapusMateri(idMateri: Integer): HasilOperasi | Pelajar menghapus materi | AKSES_DITOLAK; materi tetap terbit */
    #[TestDox('C17-UT20 Negatif: Pelajar tidak dapat menghapus materi')]
    public function test_ut20_hapus_ditolak(): void
    {
        $m = $this->materiLengkap();

        $this->assertSame('AKSES_DITOLAK', $this->sebagai('pelajar')->hapusMateri($m->id_materi)->kode);
        $this->assertSame('terbit', $m->fresh()->status_publikasi->value);
    }

    /** @uji C17-UT21 | Normal | ambilKontenLatihan(idMateri: Integer, mode: ModeLatihan): TargetLatihan | Materi terbit, mode menulis | Target berisi ꦲ dan ꦤ */
    #[TestDox('C17-UT21 Normal: konten latihan materi terbit')]
    public function test_ut21_konten_latihan(): void
    {
        $t = $this->sebagai('pelajar')->ambilKontenLatihan($this->materiLengkap()->id_materi, 'menulis');

        $this->assertSame(['ꦲ', 'ꦤ'], array_column($t->butir, 'karakter'));
    }

    /** @uji C17-UT22 | Batas | ambilKontenLatihan(idMateri: Integer, mode: ModeLatihan): TargetLatihan | Materi terbit tanpa konten | Target kosong (halaman latihan menampilkan "belum tersedia") */
    #[TestDox('C17-UT22 Batas: materi tanpa konten menghasilkan target kosong')]
    public function test_ut22_konten_latihan_kosong(): void
    {
        $this->assertTrue($this->sebagai('pelajar')->ambilKontenLatihan($this->buatMateri('Tanpa')->id_materi, 'menulis')->kosongkah());
    }

    /** @uji C17-UT23 | Negatif | ambilKontenLatihan(idMateri: Integer, mode: ModeLatihan): TargetLatihan | Materi draf dan ID tidak ada | Target kosong (tidak dapat dilatih) */
    #[TestDox('C17-UT23 Negatif: materi draf tidak dapat dilatih')]
    public function test_ut23_konten_latihan_draf(): void
    {
        $k = $this->sebagai('pelajar');

        $this->assertTrue($k->ambilKontenLatihan($this->materiLengkap('Draf', 1, StatusPublikasi::Draf)->id_materi, 'menulis')->kosongkah());
        $this->assertTrue($k->ambilKontenLatihan(999999, 'menulis')->kosongkah());
    }

    /** @uji C17-UT24 | Batas | perbaruiMateri(idMateri: Integer, data: DataKonten): HasilOperasi | Materi lama tanpa konten diperbarui | Konten baru dibuat dengan versi 1.0 */
    #[TestDox('C17-UT24 Batas: materi tanpa konten mendapat konten baru')]
    public function test_ut24_perbarui_tanpa_konten(): void
    {
        $m = $this->buatMateri('Nglegena 1');

        $this->assertTrue($this->sebagai('tim_materi')->perbaruiMateri($m->id_materi, $this->dataKonten())->berhasil);
        $this->assertSame('1.0', $m->fresh()->konten->versi_konten);
    }
}
