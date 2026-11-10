<?php

namespace Tests\Feature\Latihan;

use App\Latihan\Data\SoalLatihan;
use App\Models\AkunPengguna;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Database\Seeders\MateriSeeder;
use Tests\Feature\BantuanData;
use Tests\TestCase;

class NilaiJawabanMenulisTest extends TestCase
{
    use BantuanData, RefreshDatabase;

    private AkunPengguna $pelajar;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(MateriSeeder::class);
        $this->pelajar = $this->buatPelajar();
    }

    /** Membuka I-07 dan mengembalikan soal aktif dari session. */
    protected function bukaSoal(string $url = '/latihan/1/menulis'): object
    {
        $this->actingAs($this->pelajar)->get($url)->assertOk();
        // Session memakai serialisasi JSON; isinya harus tetap sama setelah lewat JSON.
        $this->assertEquals(json_decode(json_encode(session('latihan')), true), session('latihan'));

        return SoalLatihan::fromArray(session('latihan.soalAktif')['soal']);
    }

    /** Goresan templat soal (kotak 0–100) dipindah ke satuan kanvas (dibagi tinggi kanvas, 0–1). */
    protected function goresanBaik(SoalLatihan $soal): array
    {
        return array_map(fn ($g) => array_map(fn ($p) => ['x' => $p['x'] / 100, 'y' => $p['y'] / 100], $g), $soal->templat);
    }

    protected function goresanBuruk(): array
    {
        return [[['x' => 0.1, 'y' => 0.1], ['x' => 0.2, 'y' => 0.1]]];
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
        $soal = $this->bukaSoal();

        $this->assertSame($soal->idSoal, $this->bukaSoal()->idSoal);
    }

    public function test_id_soal_salah_409(): void
    {
        $this->bukaSoal();

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
        $soal = $this->bukaSoal();
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

    public function test_jawaban_buruk_perlu_diulang(): void
    {
        $soal = $this->bukaSoal();

        $this->postJson('/latihan/jawaban', ['idSoal' => $soal->idSoal, 'goresan' => $this->goresanBuruk()])
            ->assertOk()->assertJson(['status' => 'perlu_diulang'])
            ->assertJsonPath('umpanBalik', fn ($u) => str_ends_with($u, 'Tulis lebih dekat ke garis bantu.'));
        $this->assertSame(0, DB::table('motivasi_belajar')->count());
    }

    public function test_jawaban_baik_berhasil_tanpa_riwayat_dan_skor_body_diabaikan(): void
    {
        $soal = $this->bukaSoal();

        $this->postJson('/latihan/jawaban', ['idSoal' => $soal->idSoal, 'goresan' => $this->goresanBaik($soal), 'skor' => 5])
            ->assertOk()->assertJson(['status' => 'berhasil'])
            ->assertJsonPath('skor', fn ($s) => $s >= 70);
        $this->postJson('/latihan/jawaban', ['idSoal' => $soal->idSoal, 'goresan' => $this->goresanBuruk(), 'skor' => 100])
            ->assertJson(['status' => 'perlu_diulang']);
        $this->assertSame(0, DB::table('riwayat_latihan')->where('status', 'berhasil')->count());
    }
}
