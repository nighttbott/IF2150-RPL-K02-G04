<?php

namespace Tests\Feature\Latihan;

use App\Latihan\Data\SoalLatihan;
use App\Models\AkunPengguna;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Support\AlurLatihan;
use Database\Seeders\MateriSeeder;
use Tests\Feature\BantuanData;
use Tests\TestCase;

class NilaiJawabanMenulisTest extends TestCase
{
    use AlurLatihan, BantuanData, RefreshDatabase;

    private AkunPengguna $pelajar;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(MateriSeeder::class);
        $this->pelajar = $this->buatPelajar();
    }

    public function test_html_soal_tidak_memuat_templat(): void
    {
        $respons = $this->actingAs($this->pelajar)->get('/latihan/1/menulis')->assertOk();
        $soal = SoalLatihan::fromArray(session('latihan.soalAktif')['soal']);

        $respons->assertSee('Tulis aksara "'.$soal->latin.'"', false)->assertSee($soal->idSoal);
        $this->assertNotEmpty($soal->templat);
        $this->assertStringNotContainsString(json_encode($soal->templat[0]), $respons->getContent());
        $this->assertStringNotContainsString('templat', $respons->getContent());
    }

    public function test_refresh_memakai_soal_yang_sama(): void
    {
        $soal = $this->bukaSoal($this->pelajar);

        $this->assertSame($soal->idSoal, $this->bukaSoal($this->pelajar)->idSoal);
    }

    public function test_id_soal_salah_409(): void
    {
        $this->bukaSoal($this->pelajar);

        $this->postJson('/latihan/jawaban', ['idSoal' => 'lain', 'goresan' => $this->goresanBuruk()])
            ->assertStatus(409)->assertJson(['message' => 'Soal tidak ditemukan. Muat ulang latihan.']);
    }

    public function test_tanpa_soal_aktif_409(): void
    {
        $this->actingAs($this->pelajar)->postJson('/latihan/jawaban', ['idSoal' => 'x', 'goresan' => $this->goresanBuruk()])
            ->assertStatus(409);
    }

    public function test_payload_di_luar_batas_422(): void
    {
        $soal = $this->bukaSoal($this->pelajar);
        $kirim = fn ($goresan) => $this->postJson('/latihan/jawaban', ['idSoal' => $soal->idSoal, 'goresan' => $goresan]);
        $titik = ['x' => 0.5, 'y' => 0.5];

        $kirim([])->assertStatus(422);
        $kirim(array_fill(0, 51, [$titik, $titik]))->assertStatus(422);
        $kirim([array_fill(0, 2001, $titik)])->assertStatus(422);
        $kirim(array_fill(0, 6, array_fill(0, 1900, $titik)))->assertStatus(422);
        $kirim([[['x' => 3.5, 'y' => 0.5], $titik]])->assertStatus(422);
        $kirim([[['x' => '0.5', 'y' => 0.5], $titik]])->assertStatus(422);
        $kirim([[['y' => 0.5], $titik]])->assertStatus(422);
        $kirim([[['x' => -0.4, 'y' => 2.9, 't' => 12.5], $titik]])->assertOk();
    }

    public function test_jawaban_buruk_perlu_diulang_dicatat_tanpa_motivasi(): void
    {
        $soal = $this->bukaSoal($this->pelajar);

        $this->postJson('/latihan/jawaban', ['idSoal' => $soal->idSoal, 'goresan' => $this->goresanBuruk()])
            ->assertOk()->assertJson(['status' => 'perlu_diulang'])
            ->assertJsonPath('umpanBalik', fn ($u) => str_ends_with($u, 'Tulis lebih dekat ke garis bantu.'));
        $this->assertDatabaseHas('riwayat_latihan', [
            'id_pelajar' => $this->pelajar->id_akun, 'target_soal' => $soal->targetSoal, 'mode' => 'menulis', 'status' => 'perlu_diulang',
        ]);
        $this->assertSame(1, DB::table('riwayat_latihan')->count());
        $this->assertSame(0, DB::table('motivasi_belajar')->count());
    }

    public function test_jawaban_baik_berhasil_tanpa_riwayat_dan_skor_body_diabaikan(): void
    {
        $soal = $this->bukaSoal($this->pelajar);

        $this->postJson('/latihan/jawaban', ['idSoal' => $soal->idSoal, 'goresan' => $this->goresanBaik($soal), 'skor' => 5])
            ->assertOk()->assertJson(['status' => 'berhasil'])
            ->assertJsonPath('skor', fn ($s) => $s >= 70);
        $this->assertSame(0, DB::table('riwayat_latihan')->where('status', 'berhasil')->count());
        $this->postJson('/latihan/jawaban', ['idSoal' => $soal->idSoal, 'goresan' => $this->goresanBuruk(), 'skor' => 100])
            ->assertJson(['status' => 'perlu_diulang']);
    }
}
