<?php

namespace Tests\Feature;

use App\Enums\KategoriFeedback;
use App\Enums\StatusFeedback;
use App\Http\Controllers\FeedbackController;
use App\Models\AkunPengguna;
use App\Models\DataFeedback;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class FeedbackTest extends TestCase
{
    use RefreshDatabase;

    private const PESAN_VALID = 'Tampilannya bagus, tetapi kontras tulisannya kurang.';

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    private function data(array $ubah = []): array
    {
        return array_merge(['kategori' => 'tampilan', 'pesan' => self::PESAN_VALID], $ubah);
    }

    // ------------------------------------------------------------ akses

    public function test_tamu_diarahkan_ke_masuk(): void
    {
        $this->get(route('feedback.index'))->assertRedirect(route('masuk'));
        $this->post(route('feedback.kirim'), $this->data())->assertRedirect(route('masuk'));
    }

    public function test_tim_materi_ditolak_403(): void
    {
        $akun = AkunPengguna::factory()->timMateri()->create();

        $this->actingAs($akun)->get(route('feedback.index'))->assertForbidden();
        $this->actingAs($akun)->post(route('feedback.kirim'), $this->data())->assertForbidden();

        $this->assertDatabaseCount('data_feedback', 0);
    }

    public function test_form_tampil_untuk_pelajar_dan_pengajar(): void
    {
        foreach ([AkunPengguna::factory()->create(), AkunPengguna::factory()->pengajar()->create()] as $akun) {
            $this->actingAs($akun)->get(route('feedback.index'))
                ->assertOk()
                ->assertViewIs('feedback.index')
                ->assertViewHas('kategori', fn ($k) => count($k) === 4);
        }
    }

    // ------------------------------------------------------------ kirim berhasil

    public function test_pelajar_berhasil_mengirim_feedback(): void
    {
        $akun = AkunPengguna::factory()->create();

        $this->actingAs($akun)->post(route('feedback.kirim'), $this->data())
            ->assertRedirect(route('feedback.index'))
            ->assertSessionHas('sukses', 'Feedback terkirim. Matur nuwun!');

        $feedback = DataFeedback::query()->firstOrFail();
        $this->assertSame($akun->id_akun, (int) $feedback->id_akun);
        $this->assertSame(KategoriFeedback::Tampilan, $feedback->kategori);
        $this->assertSame(StatusFeedback::Baru, $feedback->status);
        $this->assertSame(self::PESAN_VALID, $feedback->pesan);
        $this->assertNotNull($feedback->waktu_kirim);
        $this->assertDatabaseHas('data_feedback', ['kategori' => 'tampilan', 'status' => 'baru']);
    }

    public function test_pengajar_berhasil_mengirim_feedback(): void
    {
        $akun = AkunPengguna::factory()->pengajar()->create();

        $this->actingAs($akun)->post(route('feedback.kirim'), $this->data(['kategori' => 'fitur']))
            ->assertSessionHas('sukses');

        $this->assertDatabaseHas('data_feedback', ['id_akun' => $akun->id_akun, 'kategori' => 'fitur']);
    }

    public function test_pengirim_diambil_dari_sesi_bukan_dari_form(): void
    {
        $akun = AkunPengguna::factory()->create();
        $lain = AkunPengguna::factory()->create();

        $this->actingAs($akun)->post(route('feedback.kirim'), $this->data(['id_akun' => $lain->id_akun]));

        $this->assertDatabaseHas('data_feedback', ['id_akun' => $akun->id_akun]);
        $this->assertDatabaseMissing('data_feedback', ['id_akun' => $lain->id_akun]);
    }

    public function test_pesan_disimpan_tanpa_spasi_di_tepi(): void
    {
        $akun = AkunPengguna::factory()->create();

        $hasil = app(FeedbackController::class)->simpanFeedback($akun, [
            'kategori' => 'fitur',
            'pesan'    => '   '.self::PESAN_VALID."  \n",
        ]);

        $this->assertTrue($hasil->berhasil);
        $this->assertSame(self::PESAN_VALID, DataFeedback::query()->firstOrFail()->pesan);
    }

    // ------------------------------------------------------------ validasi (Algo-013)

    #[DataProvider('isianTidakValid')]
    public function test_isian_tidak_valid_ditolak(array $ubah, string $kolom, string $pesanGalat): void
    {
        $akun = AkunPengguna::factory()->create();

        $this->actingAs($akun)->post(route('feedback.kirim'), $this->data($ubah))
            ->assertSessionHasErrors([$kolom => $pesanGalat]);

        $this->assertDatabaseCount('data_feedback', 0);
    }

    public static function isianTidakValid(): array
    {
        return [
            'kategori kosong'     => [['kategori' => ''], 'kategori', 'Pilih kategori feedback'],
            'kategori tak dikenal' => [['kategori' => 'LAINNYA'], 'kategori', 'Pilih kategori feedback'],
            'kategori huruf besar' => [['kategori' => 'FITUR'], 'kategori', 'Pilih kategori feedback'],
            'pesan kosong'        => [['pesan' => ''], 'pesan', 'Feedback 10–1.000 karakter'],
            'pesan hanya spasi'   => [['pesan' => str_repeat(' ', 20)], 'pesan', 'Feedback 10–1.000 karakter'],
            'pesan 9 karakter'    => [['pesan' => str_repeat('a', 9)], 'pesan', 'Feedback 10–1.000 karakter'],
            'pesan 1001 karakter' => [['pesan' => str_repeat('a', 1001)], 'pesan', 'Feedback 10–1.000 karakter'],
        ];
    }

    public function test_batas_panjang_pesan_diterima(): void
    {
        $akun = AkunPengguna::factory()->create();

        foreach ([10, 1000] as $panjang) {
            $this->actingAs($akun)->post(route('feedback.kirim'), $this->data(['pesan' => str_repeat('a', $panjang)]))
                ->assertSessionHas('sukses');
        }

        $this->assertDatabaseCount('data_feedback', 2);
    }

    public function test_semua_galat_dikembalikan_sekaligus_dan_isian_dipertahankan(): void
    {
        $akun = AkunPengguna::factory()->create();

        $this->actingAs($akun)->from(route('feedback.index'))
            ->post(route('feedback.kirim'), ['kategori' => '', 'pesan' => 'pendek'])
            ->assertRedirect(route('feedback.index'))
            ->assertSessionHasErrors(['kategori', 'pesan'])
            ->assertSessionHasInput('pesan', 'pendek');
    }

    public function test_simpan_feedback_memvalidasi_ulang(): void
    {
        $akun = AkunPengguna::factory()->create();

        $hasil = app(FeedbackController::class)->simpanFeedback($akun, ['kategori' => 'X', 'pesan' => 'pendek']);

        $this->assertFalse($hasil->berhasil);
        $this->assertSame('DATA_TIDAK_VALID', $hasil->kode);
        $this->assertSame('Pilih kategori feedback', $hasil->pesan['kategori']);
        $this->assertDatabaseCount('data_feedback', 0);
    }

    // ------------------------------------------------------------ gagal simpan (UC-09 Alternatif 1)

    public function test_gagal_simpan_mempertahankan_isian_dan_mencatat_log(): void
    {
        $akun = AkunPengguna::factory()->create();
        Schema::drop('data_feedback'); // memaksa penyimpanan gagal
        Log::spy();

        $this->actingAs($akun)->from(route('feedback.index'))
            ->post(route('feedback.kirim'), $this->data())
            ->assertRedirect(route('feedback.index'))
            ->assertSessionHas('galat', 'Feedback gagal terkirim. Periksa koneksi lalu coba lagi. Isi pesanmu tidak hilang.')
            ->assertSessionHasInput('pesan', self::PESAN_VALID);

        Log::shouldHaveReceived('error')
            ->withArgs(fn ($pesan, $konteks) => $pesan === 'Feedback gagal disimpan'
                && $konteks === ['id_akun' => $akun->id_akun])
            ->once();
    }

    // ------------------------------------------------------------ model (Algo-003, Algo-004)

    public function test_simpan_mengembalikan_id_dan_status_awal_baru(): void
    {
        $akun = AkunPengguna::factory()->create();
        $feedback = new DataFeedback();

        $hasil = $feedback->simpan(['id_akun' => $akun->id_akun, 'kategori' => 'materi', 'pesan' => self::PESAN_VALID]);

        $this->assertTrue($hasil->berhasil);
        $this->assertSame($feedback->id_feedback, $hasil->data['id_feedback']);
        $this->assertSame(StatusFeedback::Baru, $feedback->status);
    }

    public function test_status_hanya_boleh_maju(): void
    {
        $feedback = $this->feedbackBaru();

        $this->assertTrue($feedback->perbaruiStatus(StatusFeedback::Ditinjau)->berhasil);
        $this->assertSame(StatusFeedback::Ditinjau, $feedback->fresh()->status);
        $this->assertDatabaseHas('data_feedback', ['id_feedback' => $feedback->id_feedback, 'status' => 'ditinjau']);

        foreach ([StatusFeedback::Ditinjau, StatusFeedback::Baru] as $tidakBoleh) {
            $hasil = $feedback->perbaruiStatus($tidakBoleh);
            $this->assertFalse($hasil->berhasil);
            $this->assertSame('STATUS_TIDAK_VALID', $hasil->kode);
        }

        $this->assertTrue($feedback->perbaruiStatus(StatusFeedback::Selesai)->berhasil);
        $this->assertSame(StatusFeedback::Selesai, $feedback->fresh()->status);
    }

    public function test_status_boleh_melompat_ke_selesai(): void
    {
        $feedback = $this->feedbackBaru();

        $this->assertTrue($feedback->perbaruiStatus(StatusFeedback::Selesai)->berhasil);
    }

    public function test_pembaruan_status_bersamaan_tidak_saling_menimpa(): void
    {
        $a = $this->feedbackBaru();
        $b = DataFeedback::query()->findOrFail($a->id_feedback); // dibaca saat masih BARU

        $this->assertTrue($a->perbaruiStatus(StatusFeedback::Ditinjau)->berhasil);
        $hasil = $b->perbaruiStatus(StatusFeedback::Ditinjau);

        $this->assertFalse($hasil->berhasil);
        $this->assertSame('STATUS_BERUBAH', $hasil->kode);
    }

    private function feedbackBaru(): DataFeedback
    {
        $akun = AkunPengguna::factory()->create();
        $feedback = new DataFeedback();
        $feedback->simpan(['id_akun' => $akun->id_akun, 'kategori' => 'fitur', 'pesan' => self::PESAN_VALID]);

        return $feedback;
    }
}