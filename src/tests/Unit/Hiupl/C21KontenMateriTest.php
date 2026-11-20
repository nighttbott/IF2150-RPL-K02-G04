<?php

namespace Tests\Unit\Hiupl;

use App\Models\KontenMateri;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

/**
 * HIUPL 4.2: pengujian unit kelas C21 KontenMateri (tingkat Mendalam: versi konten, kompatibilitas, berkas audio, arsip).
 */
class C21KontenMateriTest extends TestCase
{
    use BantuanMateri, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    private function konten(?string $audio = null): KontenMateri
    {
        return $this->materiLengkap('Nglegena 1', 1, \App\Enums\StatusPublikasi::Terbit, $audio)->konten;
    }

    /** @uji C21-UT01 | Normal | ambilKonten(versiAplikasi: String): PaketMateri | Versi aplikasi 1.0, konten versi 1.0 | Paket berisi aksara ꦲ ꦤ, aturan, contoh, versi 1.0 */
    #[TestDox('C21-UT01 Normal: paket materi dimuat')]
    public function test_ut01_ambil_konten(): void
    {
        $p = $this->konten()->ambilKonten('1.0');

        $this->assertTrue($p->berhasil());
        $this->assertSame(['ꦲ', 'ꦤ'], array_column($p->aksara, 'karakter'));
        $this->assertSame('1.0', $p->versiKonten);
    }

    /** @uji C21-UT02 | Batas | ambilKonten(versiAplikasi: String): PaketMateri | Konten terbaru butuh aplikasi 2.0 tetapi versi lama (1.0) ada di arsip; aplikasi klien versi 1.5 | Versi arsip 1.0 yang kompatibel dipilih */
    #[TestDox('C21-UT02 Batas: versi arsip kompatibel dipilih untuk aplikasi lama')]
    public function test_ut02_versi_arsip(): void
    {
        $k = $this->konten();
        $data = $this->dataKonten();
        $data->aturan = 'Aturan versi baru.';
        $data->versiAplikasiMinimum = '2.0';
        $k->perbaruiKonten($data);

        $this->assertSame('1.0', $k->fresh()->ambilKonten('1.5')->versiKonten);
        $this->assertSame('1.1', $k->fresh()->ambilKonten('2.0')->versiKonten);
    }

    /** @uji C21-UT03 | Negatif | ambilKonten(versiAplikasi: String): PaketMateri | Aplikasi versi 0.9, semua konten butuh minimal 1.0 | Galat KONTEN_TIDAK_KOMPATIBEL */
    #[TestDox('C21-UT03 Negatif: aplikasi terlalu lama ditolak')]
    public function test_ut03_tidak_kompatibel(): void
    {
        $this->assertSame('KONTEN_TIDAK_KOMPATIBEL', $this->konten()->ambilKonten('0.9')->galat);
    }

    /** @uji C21-UT04 | Normal | ambilAudio(): HasilBerkas | path_audio "materi/1/ha.mp3" ada di disk penyimpanan | Berhasil dengan path berkas */
    #[TestDox('C21-UT04 Normal: audio tersedia')]
    public function test_ut04_audio_ada(): void
    {
        Storage::disk('local')->put('materi/1/ha.mp3', 'ID3');
        $h = $this->konten('materi/1/ha.mp3')->ambilAudio();

        $this->assertTrue($h->berhasil);
        $this->assertSame('materi/1/ha.mp3', $h->path);
    }

    /** @uji C21-UT05 | Batas | ambilAudio(): HasilBerkas | path_audio berupa string kosong | AUDIO_TIDAK_ADA */
    #[TestDox('C21-UT05 Batas: path audio kosong dianggap tidak ada')]
    public function test_ut05_audio_kosong(): void
    {
        $k = $this->konten();
        $k->path_audio = '';

        $this->assertSame('AUDIO_TIDAK_ADA', $k->ambilAudio()->kode);
    }

    /** @uji C21-UT06 | Negatif | ambilAudio(): HasilBerkas | path_audio terisi tetapi berkas tidak ada di disk | BERKAS_HILANG (memicu UC02 Alternatif 2) */
    #[TestDox('C21-UT06 Negatif: berkas audio hilang')]
    public function test_ut06_audio_hilang(): void
    {
        $this->assertSame('BERKAS_HILANG', $this->konten('materi/1/hilang.mp3')->ambilAudio()->kode);
    }

    /** @uji C21-UT07 | Normal | perbaruiKonten(data: DataKonten): HasilOperasi | Aturan diubah | Berhasil; versi naik 1.0 → 1.1; versi lama masuk konten_materi_arsip */
    #[TestDox('C21-UT07 Normal: perubahan konten menaikkan versi dan mengarsipkan versi lama')]
    public function test_ut07_perbarui(): void
    {
        $k = $this->konten();
        $data = $this->dataKonten();
        $data->aturan = 'Aturan baru.';

        $this->assertTrue($k->perbaruiKonten($data)->berhasil);
        $this->assertSame('1.1', $k->fresh()->versi_konten);
        $this->assertDatabaseHas('konten_materi_arsip', ['id_konten' => $k->id_konten, 'versi_konten' => '1.0']);
    }

    /** @uji C21-UT08 | Batas | perbaruiKonten(data: DataKonten): HasilOperasi | Data identik dengan konten sekarang | Berhasil dengan pesan "Tidak ada perubahan"; versi tetap 1.0; tidak ada arsip */
    #[TestDox('C21-UT08 Batas: data identik tidak menaikkan versi')]
    public function test_ut08_tanpa_perubahan(): void
    {
        $k = $this->konten();
        $h = $k->perbaruiKonten($this->dataKonten());

        $this->assertSame('Tidak ada perubahan', $h->pesan);
        $this->assertSame('1.0', $k->fresh()->versi_konten);
        $this->assertSame(0, DB::table('konten_materi_arsip')->count());
    }

    /** @uji C21-UT09 | Negatif | perbaruiKonten(data: DataKonten): HasilOperasi | Aturan berisi spasi saja | KONTEN_TIDAK_LENGKAP; konten lama tidak berubah */
    #[TestDox('C21-UT09 Negatif: konten tidak lengkap ditolak')]
    public function test_ut09_tidak_lengkap(): void
    {
        $k = $this->konten();
        $data = $this->dataKonten();
        $data->aturan = '   ';

        $this->assertSame('KONTEN_TIDAK_LENGKAP', $k->perbaruiKonten($data)->kode);
        $this->assertSame('Tulis dari kiri ke kanan.', $k->fresh()->aturan);
    }

    /** @uji C21-UT10 | Normal | simpanKonten(data: DataKonten): HasilOperasi | Konten lengkap untuk materi baru | Berhasil; versi_konten 1.0 */
    #[TestDox('C21-UT10 Normal: konten baru berversi 1.0')]
    public function test_ut10_simpan_konten(): void
    {
        $this->assertSame('1.0', $this->konten()->versi_konten);
    }

    /** @uji C21-UT11 | Negatif | simpanKonten(data: DataKonten): HasilOperasi | Bentuk aksara kosong | KONTEN_TIDAK_LENGKAP; tidak ada baris konten */
    #[TestDox('C21-UT11 Negatif: simpan konten tanpa bentuk aksara ditolak')]
    public function test_ut11_simpan_tidak_lengkap(): void
    {
        $data = $this->dataKonten();
        $data->bentukAksara = '';
        $m = $this->materiLengkap('Lain');
        DB::table('konten_materi')->delete();

        $this->assertSame('KONTEN_TIDAK_LENGKAP', (new KontenMateri(['id_materi' => $m->id_materi]))->simpanKonten($data)->kode);
        $this->assertSame(0, KontenMateri::count());
    }

    /** @uji C21-UT12 | Batas | simpanKonten(data: DataKonten): HasilOperasi | Bentuk, aturan, dan contoh diapit spasi | Tersimpan setelah di-trim */
    #[TestDox('C21-UT12 Batas: konten di-trim sebelum disimpan')]
    public function test_ut12_simpan_trim(): void
    {
        $data = $this->dataKonten();
        $data->aturan = '   Aturan berspasi.   ';
        $m = new \App\Models\MateriAksara;
        $m->simpan($data->metadata());
        $k = new KontenMateri(['id_materi' => $m->id_materi]);
        $k->simpanKonten($data);

        $this->assertSame('Aturan berspasi.', $k->fresh()->aturan);
    }

    /** @uji C21-UT13 | Normal | arsipkanVersi(): HasilOperasi | Konten versi 1.0 diarsipkan | Berhasil; baris konten_materi_arsip berisi salinan versi 1.0 */
    #[TestDox('C21-UT13 Normal: versi konten diarsipkan')]
    public function test_ut13_arsip(): void
    {
        $k = $this->konten();

        $this->assertTrue($k->arsipkanVersi()->berhasil);
        $this->assertDatabaseHas('konten_materi_arsip', ['id_konten' => $k->id_konten, 'versi_konten' => '1.0', 'aturan' => 'Tulis dari kiri ke kanan.']);
    }

    /** @uji C21-UT14 | Batas | arsipkanVersi(): HasilOperasi | Konten diperbarui dua kali (1.0 → 1.1 → 1.2) | Arsip berisi versi 1.0 dan 1.1; konten aktif 1.2 */
    #[TestDox('C21-UT14 Batas: pembaruan berulang menyimpan semua versi lama')]
    public function test_ut14_arsip_berulang(): void
    {
        $k = $this->konten();
        foreach (['Aturan dua.', 'Aturan tiga.'] as $a) {
            $d = $this->dataKonten();
            $d->aturan = $a;
            $k->perbaruiKonten($d);
        }

        $this->assertSame(['1.0', '1.1'], DB::table('konten_materi_arsip')->orderBy('versi_konten')->pluck('versi_konten')->all());
        $this->assertSame('1.2', $k->fresh()->versi_konten);
    }

    /** @uji C21-UT15 | Negatif | arsipkanVersi(): HasilOperasi | Tabel arsip tidak tersedia saat konten diperbarui | Operasi gagal dengan galat basis data dan konten aktif tidak tertimpa */
    #[TestDox('C21-UT15 Negatif: tanpa arsip konten tidak ditimpa')]
    public function test_ut15_arsip_gagal(): void
    {
        $k = $this->konten();
        DB::statement('DROP TABLE konten_materi_arsip');
        $d = $this->dataKonten();
        $d->aturan = 'Aturan baru.';
        try {
            $k->perbaruiKonten($d);
        } catch (\Throwable) {
        }

        $this->assertSame('1.0', KontenMateri::find($k->id_konten)->versi_konten);
    }
}
