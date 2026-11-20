<?php

namespace Tests\Unit\Hiupl;

use App\Http\Controllers\FeedbackController;
use App\Models\AkunPengguna;
use App\Models\DataFeedback;
use App\Support\HasilOperasi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\Feature\BantuanData;
use Tests\TestCase;

/**
 * HIUPL 4.2: pengujian unit kelas C18 FeedbackController (tingkat Mendalam: validasi masukan dan penanganan kegagalan, KNF05).
 */
class C18FeedbackControllerTest extends TestCase
{
    use BantuanData, RefreshDatabase;

    private function k(): FeedbackController
    {
        return app(FeedbackController::class);
    }

    /** @uji C18-UT01 | Normal | validasiFeedback(data: MasukanFeedback): HasilValidasi | Pelajar, kategori fitur, pesan 30 karakter | Tidak ada galat */
    #[TestDox('C18-UT01 Normal: masukan valid lolos validasi')]
    public function test_ut01_valid(): void
    {
        $this->assertSame([], $this->k()->validasiFeedback($this->buatPelajar(), ['kategori' => 'fitur', 'pesan' => 'Tolong tambah aksara Bali ya.']));
    }

    /** @uji C18-UT02 | Batas | validasiFeedback(data: MasukanFeedback): HasilValidasi | Pesan tepat 10 dan 1.000 karakter (valid); 9 dan 1.001 karakter; 10 karakter diapit spasi | 10 dan 1.000: valid; 9 dan 1.001: galat pesan; spasi tidak dihitung */
    #[TestDox('C18-UT02 Batas: panjang pesan 10..1000 karakter')]
    public function test_ut02_batas_panjang(): void
    {
        $p = $this->buatPelajar();
        $cek = fn (string $pesan) => $this->k()->validasiFeedback($p, ['kategori' => 'materi', 'pesan' => $pesan]);

        $this->assertSame([], $cek(str_repeat('a', 10)));
        $this->assertSame([], $cek(str_repeat('a', 1000)));
        $this->assertArrayHasKey('pesan', $cek(str_repeat('a', 9)));
        $this->assertArrayHasKey('pesan', $cek(str_repeat('a', 1001)));
        $this->assertArrayHasKey('pesan', $cek('   '.str_repeat('a', 9).'   '));
    }

    /** @uji C18-UT03 | Negatif | validasiFeedback(data: MasukanFeedback): HasilValidasi | (a) kategori "lainnya" dan pesan kosong; (b) akun Tim Materi; (c) tanpa akun | (a) galat kategori dan pesan; (b)(c) HttpException 403 */
    #[TestDox('C18-UT03 Negatif: kategori salah, pesan kosong, dan peran tanpa hak ditolak')]
    public function test_ut03_tidak_valid(): void
    {
        $galat = $this->k()->validasiFeedback($this->buatPelajar(), ['kategori' => 'lainnya', 'pesan' => '']);
        $this->assertSame(['kategori' => 'Pilih kategori feedback', 'pesan' => 'Feedback 10–1.000 karakter'], $galat);

        foreach ([AkunPengguna::factory()->timMateri()->create(), null] as $akun) {
            try {
                $this->k()->validasiFeedback($akun, ['kategori' => 'fitur', 'pesan' => 'Pesan uji panjang.']);
                $this->fail('Seharusnya 403');
            } catch (HttpException $e) {
                $this->assertSame(403, $e->getStatusCode());
            }
        }
    }

    /** @uji C18-UT04 | Normal | simpanFeedback(data: MasukanFeedback): HasilOperasi | Pengajar mengirim kategori tampilan | Berhasil; baris data_feedback dengan id_akun Pengajar dan pesan ter-trim */
    #[TestDox('C18-UT04 Normal: feedback valid tersimpan')]
    public function test_ut04_simpan(): void
    {
        $g = $this->buatPengajar();
        $h = $this->k()->simpanFeedback($g, ['kategori' => 'tampilan', 'pesan' => '  Tombolnya kurang besar.  ']);

        $this->assertTrue($h->berhasil);
        $this->assertDatabaseHas('data_feedback', ['id_akun' => $g->id_akun, 'pesan' => 'Tombolnya kurang besar.', 'status' => 'baru']);
    }

    /** @uji C18-UT05 | Batas | simpanFeedback(data: MasukanFeedback): HasilOperasi | Pesan 1.000 karakter beraksara Jawa (multibyte) | Tersimpan utuh 1.000 karakter */
    #[TestDox('C18-UT05 Batas: pesan multibyte 1000 karakter tersimpan utuh')]
    public function test_ut05_simpan_multibyte(): void
    {
        $pesan = str_repeat('ꦲ', 1000);
        $this->k()->simpanFeedback($this->buatPelajar(), ['kategori' => 'materi', 'pesan' => $pesan]);

        $this->assertSame(1000, mb_strlen(DataFeedback::first()->pesan));
    }

    /** @uji C18-UT06 | Negatif | simpanFeedback(data: MasukanFeedback): HasilOperasi | (a) pesan 5 karakter; (b) tabel data_feedback tidak dapat ditulis (tabel dihapus) | (a) DATA_TIDAK_VALID; (b) SIMPAN_GAGAL dan galat tercatat di log */
    #[TestDox('C18-UT06 Negatif: data tidak valid dan kegagalan basis data ditangani')]
    public function test_ut06_simpan_gagal(): void
    {
        $p = $this->buatPelajar();
        $this->assertSame('DATA_TIDAK_VALID', $this->k()->simpanFeedback($p, ['kategori' => 'fitur', 'pesan' => 'pendk'])->kode);

        \Illuminate\Support\Facades\Log::spy();
        DB::statement('DROP TABLE data_feedback');
        $this->assertSame('SIMPAN_GAGAL', $this->k()->simpanFeedback($p, ['kategori' => 'fitur', 'pesan' => 'Pesan yang cukup panjang.'])->kode);
        \Illuminate\Support\Facades\Log::shouldHaveReceived('error')->once();
    }

    /** @uji C18-UT07 | Normal | kirimKonfirmasi(hasil: HasilOperasi): void | HasilOperasi sukses | berhasil true dengan pesan "Feedback terkirim. Matur nuwun!" */
    #[TestDox('C18-UT07 Normal: konfirmasi berhasil')]
    public function test_ut07_konfirmasi_berhasil(): void
    {
        $this->assertSame(['berhasil' => true, 'pesan' => 'Feedback terkirim. Matur nuwun!'], $this->k()->kirimKonfirmasi(HasilOperasi::sukses()));
    }

    /** @uji C18-UT08 | Negatif | kirimKonfirmasi(hasil: HasilOperasi): void | HasilOperasi gagal SIMPAN_GAGAL | berhasil false; pesan meminta mencoba lagi dan menyatakan isian tidak hilang */
    #[TestDox('C18-UT08 Negatif: konfirmasi gagal memberi pesan coba lagi')]
    public function test_ut08_konfirmasi_gagal(): void
    {
        $k = $this->k()->kirimKonfirmasi(HasilOperasi::gagal('SIMPAN_GAGAL', 'x'));

        $this->assertFalse($k['berhasil']);
        $this->assertStringContainsString('coba lagi', $k['pesan']);
    }

    /** @uji C18-UT09 | Batas | kirimKonfirmasi(hasil: HasilOperasi): void | Alur HTTP POST /feedback saat penyimpanan gagal | Kembali ke formulir dengan pesan galat dan isian lama dipertahankan (old input) */
    #[TestDox('C18-UT09 Batas: kegagalan HTTP mempertahankan isian')]
    public function test_ut09_http_gagal_isian_tetap(): void
    {
        $p = $this->buatPelajar();
        DB::statement('DROP TABLE data_feedback');

        $this->actingAs($p)->from(route('feedback.index'))->post(route('feedback.kirim'), ['kategori' => 'fitur', 'pesan' => 'Pesan yang cukup panjang.'])
            ->assertRedirect(route('feedback.index'))->assertSessionHas('galat')->assertSessionHasInput('pesan', 'Pesan yang cukup panjang.');
    }
}
