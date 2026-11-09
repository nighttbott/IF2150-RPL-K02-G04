<?php

namespace Tests\Feature;

use App\Enums\JenisAksara;
use App\Enums\StatusPublikasi;
use App\Http\Controllers\MateriController;
use App\Models\KontenMateri;
use App\Models\MateriAksara;
use App\Models\User;
use App\Support\Materi\DataKonten;
use App\Support\Materi\FilterMateri;
use App\Support\Materi\PengolahKonten;
use App\Support\Materi\TemplateAksara;
use Database\Seeders\MateriSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Pengujian modul materi (UC-02 Mempelajari Materi, UC-06 Memperbarui Materi).
 * Peran diberikan lewat atribut `peran` pada user (sementara, sampai AkunPengguna milik Ryuza tersedia).
 */
class MateriTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    private function pengguna(string $peran): User
    {
        return User::factory()->create()->forceFill(['peran' => $peran]);
    }

    private function buatMateri(string $nama, JenisAksara $jenis, int $tingkat, StatusPublikasi $status): MateriAksara
    {
        $data = new DataKonten($nama, $jenis, $tingkat, $status, 'ꦲ ꦤ', 'Aturan dasar.', 'ꦲꦤ = "ana"');
        $materi = new MateriAksara;
        $materi->simpan($data->metadata());
        (new KontenMateri(['id_materi' => $materi->id_materi]))->simpanKonten($data);

        return $materi;
    }

    private function dataBaru(string $nama = 'Nglegena: ha na', StatusPublikasi $status = StatusPublikasi::Draf): DataKonten
    {
        $teks = PengolahKonten::pisahAturanContoh('Tulis dari kiri ke kanan. Contoh: ꦲꦤ = "ana"');

        return new DataKonten($nama, JenisAksara::Jawa, 1, $status, PengolahKonten::bentukDariNama(JenisAksara::Jawa, $nama), $teks['aturan'], $teks['contoh']);
    }

    private function sebagai(string $peran): MateriController
    {
        $this->actingAs($this->pengguna($peran));

        return app(MateriController::class);
    }

    /* ---------- Pelajar: daftar dan detail (UC-02) ---------- */

    public function test_pelajar_hanya_melihat_materi_terbit_sesuai_filter(): void
    {
        $this->buatMateri('Terbit Jawa 1', JenisAksara::Jawa, 1, StatusPublikasi::Terbit);
        $this->buatMateri('Draf Jawa 1', JenisAksara::Jawa, 1, StatusPublikasi::Draf);
        $this->buatMateri('Arsip Jawa 1', JenisAksara::Jawa, 1, StatusPublikasi::Arsip);
        $this->buatMateri('Terbit Sunda 1', JenisAksara::Sunda, 1, StatusPublikasi::Terbit);
        $this->actingAs($this->pengguna('pelajar'));

        $this->get(route('materi.index', ['jenis' => 'jawa', 'tingkat' => 1]))
            ->assertOk()->assertSee('Terbit Jawa 1')
            ->assertDontSee('Draf Jawa 1')->assertDontSee('Arsip Jawa 1')->assertDontSee('Terbit Sunda 1');
    }

    public function test_daftar_kosong_menampilkan_pesan_bukan_galat(): void
    {
        $this->actingAs($this->pengguna('pelajar'));

        $this->get(route('materi.index', ['jenis' => 'sunda', 'tingkat' => 3]))
            ->assertOk()->assertSee('Belum ada materi untuk pilihan ini');
    }

    public function test_pelajar_tidak_bisa_memfilter_draf(): void
    {
        $this->buatMateri('Draf Jawa 1', JenisAksara::Jawa, 1, StatusPublikasi::Draf);

        $daftar = $this->sebagai('pelajar')->ambilDaftarMateri(FilterMateri::semuaTermasukDraf());

        $this->assertCount(0, $daftar);
    }

    public function test_detail_materi_terbit_menampilkan_isi_dan_draf_ditolak_untuk_pelajar(): void
    {
        $terbit = $this->buatMateri('Terbit Jawa 1', JenisAksara::Jawa, 1, StatusPublikasi::Terbit);
        $draf = $this->buatMateri('Draf Jawa 1', JenisAksara::Jawa, 1, StatusPublikasi::Draf);
        $this->actingAs($this->pengguna('pelajar'));

        $this->get(route('materi.show', $terbit->id_materi))->assertOk()->assertSee('Aturan dasar.');
        $this->get(route('materi.show', $draf->id_materi))->assertNotFound();
    }

    public function test_konten_dipilih_menurut_versi_aplikasi(): void
    {
        $materi = $this->buatMateri('Terbit Jawa 1', JenisAksara::Jawa, 1, StatusPublikasi::Terbit);
        $konten = $materi->konten;
        $konten->arsipkanVersi();
        $konten->update(['versi_konten' => '2.0', 'versi_aplikasi_minimum' => '2.0', 'aturan' => 'Aturan versi baru']);

        $lama = $konten->fresh()->ambilKonten('1.5');
        $baru = $konten->fresh()->ambilKonten('2.0');

        $this->assertSame('Aturan dasar.', $lama->aturan);
        $this->assertSame('Aturan versi baru', $baru->aturan);
    }

    public function test_target_latihan_membawa_template_goresan(): void
    {
        $this->seed(MateriSeeder::class);
        $materi = MateriAksara::where('nama_materi', 'Nglegena: ha na ca ra ka')->firstOrFail();

        $target = $materi->ambilTargetLatihan('menulis');

        $this->assertCount(5, $target->butir);
        $this->assertSame('ha', $target->butir[0]['bunyi']);
        $this->assertNotEmpty($target->butir[0]['template'][0]);
        $this->assertSame('ana', $materi->ambilTargetLatihan('merangkai')->butir[0]['bunyi']);
    }

    public function test_seed_aman_dijalankan_ulang_dan_hanya_menghasilkan_materi_terbit(): void
    {
        $this->seed(MateriSeeder::class);
        $jumlah = MateriAksara::count();
        $this->seed(MateriSeeder::class);

        $this->assertSame($jumlah, MateriAksara::count());
        $this->assertSame(0, MateriAksara::where('status_publikasi', '!=', 'terbit')->count());
    }

    public function test_template_aksara_lengkap_untuk_tingkat_dasar(): void
    {
        foreach (['ha', 'na', 'ca', 'ra', 'ka', 'da', 'ta', 'sa', 'wa', 'la', 'pa', 'dha', 'ja', 'ya', 'nya', 'ma', 'ga', 'ba', 'tha', 'nga'] as $kode) {
            $karakter = TemplateAksara::karakterDariKode(JenisAksara::Jawa, $kode);
            $this->assertNotNull($karakter, "kode {$kode} hilang");
            $this->assertNotEmpty(TemplateAksara::cari(JenisAksara::Jawa, $karakter)['goresan']);
        }
    }

    /* ---------- Tim Materi: tambah, terbit, arsip (UC-06) ---------- */

    public function test_pelajar_tidak_berhak_mengelola_materi(): void
    {
        $hasil = $this->sebagai('pelajar')->tambahMateri($this->dataBaru());
        $this->assertSame('AKSES_DITOLAK', $hasil->kode);
        $this->assertSame(0, MateriAksara::count());

        $this->get(route('materi.kelola'))->assertForbidden();
    }

    public function test_tambah_materi_tersimpan_sebagai_draf_dengan_konten_versi_1(): void
    {
        $hasil = $this->sebagai('tim_materi')->tambahMateri($this->dataBaru());

        $this->assertTrue($hasil->berhasil);
        $materi = MateriAksara::findOrFail($hasil->data['idMateri']);
        $this->assertSame(StatusPublikasi::Draf, $materi->status_publikasi);
        $this->assertSame('1.0', $materi->konten->versi_konten);
        $this->assertSame('ꦲ ꦤ', $materi->konten->bentuk_aksara);
    }

    public function test_materi_ganda_ditolak(): void
    {
        $c = $this->sebagai('tim_materi');
        $c->tambahMateri($this->dataBaru());

        $this->assertSame('DUPLIKAT', $c->tambahMateri($this->dataBaru())->kode);
        $this->assertSame(1, MateriAksara::count());
    }

    public function test_konten_tidak_lengkap_membatalkan_seluruh_transaksi(): void
    {
        $data = $this->dataBaru('Tanpa daftar aksara'); // tidak ada ":" sehingga bentukAksara kosong

        $hasil = $this->sebagai('tim_materi')->tambahMateri($data);

        $this->assertSame('KONTEN_TIDAK_LENGKAP', $hasil->kode);
        $this->assertSame(0, MateriAksara::count());
        $this->assertSame(0, KontenMateri::count());
    }

    public function test_format_dan_ukuran_berkas_divalidasi(): void
    {
        $c = $this->sebagai('tim_materi');

        $this->assertFalse($c->validasiFormatFile(UploadedFile::fake()->create('virus.exe', 10))->valid);
        $this->assertFalse($c->validasiFormatFile(UploadedFile::fake()->create('besar.mp3', 10241))->valid);
        $this->assertTrue($c->validasiFormatFile(UploadedFile::fake()->create('audio.mp3', 10240))->valid);
        $this->assertTrue($c->validasiFormatFile(UploadedFile::fake()->create('gambar.png', 5))->valid);
        $this->assertFalse($c->validasiFormatFile(UploadedFile::fake()->createWithContent('rusak.json', '{bukan json'))->valid);
        $this->assertTrue($c->validasiFormatFile(UploadedFile::fake()->createWithContent('konten.json', '{"aturan":"x"}'))->valid);
    }

    public function test_berkas_tidak_valid_tidak_membuat_materi(): void
    {
        $hasil = $this->sebagai('tim_materi')->tambahMateri($this->dataBaru(), UploadedFile::fake()->create('virus.exe', 10));

        $this->assertSame('FORMAT_TIDAK_VALID', $hasil->kode);
        $this->assertSame('Jenis berkas tidak didukung', $hasil->pesan);
        $this->assertSame(0, MateriAksara::count());
    }

    public function test_audio_mp3_disimpan_dan_dapat_diputar_pelajar_setelah_terbit(): void
    {
        $c = $this->sebagai('tim_materi');
        $hasil = $c->tambahMateri($this->dataBaru(), UploadedFile::fake()->create('suara.mp3', 20));
        $id = $hasil->data['idMateri'];
        $this->assertTrue(Storage::disk('local')->exists("materi/{$id}/audio.mp3"));

        $this->assertFalse($c->ambilAudioPengucapan($id)->berhasil, 'draf tidak boleh diputar');
        $c->perbaruiMateri($id, $this->dataBaru('Nglegena: ha na', StatusPublikasi::Terbit));
        $this->assertTrue($c->ambilAudioPengucapan($id)->berhasil);

        $this->actingAs($this->pengguna('pelajar'));
        $this->get(route('materi.audio', $id))->assertOk();
    }

    public function test_terbitkan_membuat_materi_muncul_untuk_pelajar(): void
    {
        $id = $this->sebagai('tim_materi')->tambahMateri($this->dataBaru())->data['idMateri'];

        $this->post(route('materi.terbitkan', $id))->assertRedirect(route('materi.kelola'));

        $this->assertSame(StatusPublikasi::Terbit, MateriAksara::find($id)->status_publikasi);
        $this->assertSame('1.0', MateriAksara::find($id)->konten->versi_konten, 'menerbitkan tidak menaikkan versi konten');
        $this->actingAs($this->pengguna('pelajar'));
        $this->get(route('materi.index', ['jenis' => 'jawa', 'tingkat' => 1]))->assertSee('Nglegena: ha na');
    }

    public function test_ubah_konten_mengarsipkan_versi_lama_dan_menaikkan_versi(): void
    {
        $c = $this->sebagai('tim_materi');
        $id = $c->tambahMateri($this->dataBaru())->data['idMateri'];
        $ubah = $this->dataBaru();
        $ubah->aturan = 'Aturan yang sudah direvisi.';

        $hasil = $c->perbaruiMateri($id, $ubah);

        $this->assertTrue($hasil->berhasil);
        $this->assertSame('1.1', KontenMateri::first()->versi_konten);
        $this->assertDatabaseHas('konten_materi_arsip', ['id_materi' => $id, 'versi_konten' => '1.0']);
    }

    public function test_arsipkan_tidak_menghapus_baris_tetapi_menyembunyikan_dari_pelajar(): void
    {
        $c = $this->sebagai('tim_materi');
        $id = $c->tambahMateri($this->dataBaru('Nglegena: ha na', StatusPublikasi::Terbit))->data['idMateri'];

        $this->assertTrue($c->hapusMateri($id)->berhasil);

        $this->assertDatabaseHas('materi_aksara', ['id_materi' => $id, 'status_publikasi' => 'arsip']);
        $this->assertCount(0, $c->ambilDaftarMateri(FilterMateri::semuaTermasukDraf()));
        $this->assertSame('TIDAK_DITEMUKAN', $c->hapusMateri($id)->kode);
    }

    public function test_form_unggah_menyimpan_materi_lewat_http(): void
    {
        $this->actingAs($this->pengguna('tim_materi'));

        $this->post(route('materi.simpan'), [
            'nama_materi' => 'Nglegena: ha na',
            'jenis_aksara' => 'jawa',
            'tingkat_kesulitan' => 1,
            'aturan_contoh' => 'Tulis dari kiri ke kanan. Contoh: ꦲꦤ = "ana"',
        ])->assertRedirect(route('materi.kelola'));

        $this->assertDatabaseHas('materi_aksara', ['nama_materi' => 'Nglegena: ha na', 'status_publikasi' => 'draf']);
        $this->get(route('materi.kelola'))->assertSee('Nglegena: ha na')->assertSee('Terbitkan');
    }

    public function test_form_unggah_menolak_isian_kosong(): void
    {
        $this->actingAs($this->pengguna('tim_materi'));

        $this->post(route('materi.simpan'), ['nama_materi' => ''])
            ->assertSessionHasErrors(['nama_materi', 'jenis_aksara', 'tingkat_kesulitan', 'aturan_contoh']);
    }

    public function test_rekomendasi_membuka_tingkat_berikutnya_setelah_tingkat_bawah_tuntas(): void
    {
        $a = $this->buatMateri('T1', JenisAksara::Jawa, 1, StatusPublikasi::Terbit);
        $b = $this->buatMateri('T2', JenisAksara::Jawa, 2, StatusPublikasi::Terbit);

        $belumMulai = MateriAksara::ambilMateriRekomendasi([]);
        $setelahT1 = MateriAksara::ambilMateriRekomendasi([$a->id_materi => 80]);

        $this->assertSame(['T1'], array_map(fn ($m) => $m->nama_materi, $belumMulai));
        $this->assertSame(['T2'], array_map(fn ($m) => $m->nama_materi, $setelahT1));
    }
}
