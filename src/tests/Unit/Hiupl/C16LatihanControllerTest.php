<?php

namespace Tests\Unit\Hiupl;

use App\Latihan\AkurasiGoresan;
use App\Latihan\PemeriksaBadge;
use App\Models\AkunPengguna;
use App\Models\MotivasiBelajar;
use App\Services\Latihan\PenilaiJawaban;
use App\Services\Latihan\SelesaikanLatihan;
use Database\Seeders\MateriSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\Feature\BantuanData;
use Tests\Support\AlurLatihan;
use Tests\TestCase;

/**
 * HIUPL 4.2: pengujian unit kelas C16 LatihanController (tingkat Mendalam: algoritma penilaian, poin, badge).
 * Logika C16 diwujudkan oleh LatihanController, LatihanNonKanvasController, AkurasiGoresan, PenilaiJawaban,
 * SelesaikanLatihan, dan PemeriksaBadge; operasi DPPL diuji pada unit-unit tersebut.
 */
class C16LatihanControllerTest extends TestCase
{
    use AlurLatihan, BantuanData, RefreshDatabase;

    private AkunPengguna $pelajar;

    private static array $templat;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(MateriSeeder::class);
        $this->pelajar = $this->buatPelajar();
        $json = json_decode(file_get_contents(base_path('resources/aksara/template_aksara.json')), true);
        self::$templat = array_column($json['aksara'], null, 'karakter');
    }

    private function akurasi(array $goresan, string $aksara): float
    {
        return (new AkurasiGoresan(200, 0.05, 0.25, 0.5))->hitung($goresan, self::$templat[$aksara]['tengah'], self::$templat[$aksara]['goresan']);
    }

    /** @uji C16-UT01 | Normal | siapkanSoal(idMateri: Integer, mode: ModeLatihan): SoalLatihan | Pelajar membuka GET /latihan/1/menulis | 200; soal aktif tersimpan di sesi; templat goresan tidak dikirim ke HTML */
    #[TestDox('C16-UT01 Normal: soal menulis disiapkan tanpa membocorkan templat')]
    public function test_ut01_siapkan_soal(): void
    {
        $soal = $this->bukaSoal($this->pelajar);

        $this->assertNotEmpty($soal->templat);
        $this->assertStringNotContainsString('templat', $this->get('/latihan/1/menulis')->getContent());
    }

    /** @uji C16-UT02 | Batas | siapkanSoal(idMateri: Integer, mode: ModeLatihan): SoalLatihan | Halaman soal dimuat ulang (refresh) | Soal yang sama dipakai lagi (idSoal tetap) */
    #[TestDox('C16-UT02 Batas: refresh memakai soal yang sama')]
    public function test_ut02_soal_tetap(): void
    {
        $this->assertSame($this->bukaSoal($this->pelajar)->idSoal, $this->bukaSoal($this->pelajar)->idSoal);
    }

    /** @uji C16-UT03 | Negatif | siapkanSoal(idMateri: Integer, mode: ModeLatihan): SoalLatihan | (a) tamu tanpa login; (b) Pengajar membuka latihan | (a) dialihkan ke /masuk; (b) 403 */
    #[TestDox('C16-UT03 Negatif: tamu dan Pengajar tidak dapat membuka soal')]
    public function test_ut03_soal_ditolak(): void
    {
        $this->get('/latihan/1/menulis')->assertRedirect(route('masuk'));
        $this->actingAs($this->buatPengajar())->get('/latihan/1/menulis')->assertForbidden();
    }

    /** @uji C16-UT04 | Normal | hitungAkurasiGoresan(goresan: List<Goresan>, templat: TemplatAksara): Decimal | Goresan tepat di garis tengah ꦲ, ꦏ, ꦤ | Skor 100.0 */
    #[TestDox('C16-UT04 Normal: goresan tepat di garis tengah bernilai 100')]
    public function test_ut04_akurasi_tepat(): void
    {
        foreach (['ꦲ', 'ꦏ', 'ꦤ'] as $a) {
            $this->assertSame(100.0, $this->akurasi(self::$templat[$a]['tengah'], $a), $a);
        }
    }

    /** @uji C16-UT05 | Batas | hitungAkurasiGoresan(goresan: List<Goresan>, templat: TemplatAksara): Decimal | Goresan kosong, satu titik, templat kosong; goresan separuh huruf | Kasus tepi: 0.0; separuh: < 70 (ambang lulus) */
    #[TestDox('C16-UT05 Batas: kasus tepi bernilai 0, goresan separuh di bawah ambang')]
    public function test_ut05_akurasi_tepi(): void
    {
        $ak = new AkurasiGoresan(200, 0.05, 0.25, 0.5);
        $this->assertSame(0.0, $ak->hitung([], [[['x' => 10, 'y' => 10], ['x' => 90, 'y' => 90]]]));
        $this->assertSame(0.0, $ak->hitung([[['x' => 0.3, 'y' => 0.4]]], [[['x' => 10, 'y' => 10], ['x' => 90, 'y' => 90]]]));
        $this->assertSame(0.0, $ak->hitung([[['x' => 0, 'y' => 0], ['x' => 1, 'y' => 1]]], []));
        $separuh = array_map(fn ($g) => array_slice($g, 0, intdiv(count($g), 2)), self::$templat['ꦲ']['tengah']);
        $this->assertLessThan(70, $this->akurasi($separuh, 'ꦲ'));
    }

    /** @uji C16-UT06 | Negatif | hitungAkurasiGoresan(goresan: List<Goresan>, templat: TemplatAksara): Decimal | Menulis ꦗ untuk soal ꦲ, dan ꦕ untuk soal ꦫ | Skor < 70 (tidak lulus) */
    #[TestDox('C16-UT06 Negatif: aksara lain tidak lulus')]
    public function test_ut06_akurasi_aksara_lain(): void
    {
        $this->assertLessThan(70, $this->akurasi(self::$templat['ꦗ']['tengah'], 'ꦲ'));
        $this->assertLessThan(70, $this->akurasi(self::$templat['ꦕ']['tengah'], 'ꦫ'));
    }

    /** @uji C16-UT07 | Normal | nilaiJawaban(jawaban: JawabanLatihan): HasilPenilaian | POST /latihan/jawaban dengan goresan sesuai templat soal | 200; status berhasil; skor ≥ 70 dihitung server */
    #[TestDox('C16-UT07 Normal: jawaban goresan baik berhasil')]
    public function test_ut07_nilai_berhasil(): void
    {
        $soal = $this->bukaSoal($this->pelajar);

        $this->postJson('/latihan/jawaban', ['idSoal' => $soal->idSoal, 'goresan' => $this->goresanBaik($soal), 'skor' => 5])
            ->assertOk()->assertJson(['status' => 'berhasil'])->assertJsonPath('skor', fn ($s) => $s >= 70);
    }

    /** @uji C16-UT08 | Batas | nilaiJawaban(jawaban: JawabanLatihan): HasilPenilaian | 51 goresan (batas 50), 2001 titik dalam satu goresan (batas 2000), koordinat x = 3.5 | 422 untuk semuanya */
    #[TestDox('C16-UT08 Batas: payload melewati batas ditolak 422')]
    public function test_ut08_nilai_batas_payload(): void
    {
        $soal = $this->bukaSoal($this->pelajar);
        $t = ['x' => 0.5, 'y' => 0.5];
        $kirim = fn ($g) => $this->postJson('/latihan/jawaban', ['idSoal' => $soal->idSoal, 'goresan' => $g]);

        $kirim(array_fill(0, 51, [$t, $t]))->assertStatus(422);
        $kirim([array_fill(0, 2001, $t)])->assertStatus(422);
        $kirim([[['x' => 3.5, 'y' => 0.5], $t]])->assertStatus(422);
    }

    /** @uji C16-UT09 | Negatif | nilaiJawaban(jawaban: JawabanLatihan): HasilPenilaian | (a) goresan jauh dari templat; (b) idSoal bukan soal aktif | (a) perlu_diulang dan riwayat tercatat; (b) 409 SOAL_TIDAK_DITEMUKAN */
    #[TestDox('C16-UT09 Negatif: jawaban buruk perlu diulang, soal salah 409')]
    public function test_ut09_nilai_gagal(): void
    {
        $soal = $this->bukaSoal($this->pelajar);

        $this->postJson('/latihan/jawaban', ['idSoal' => $soal->idSoal, 'goresan' => $this->goresanBuruk()])->assertJson(['status' => 'perlu_diulang']);
        $this->assertDatabaseHas('riwayat_latihan', ['id_pelajar' => $this->pelajar->id_akun, 'status' => 'perlu_diulang']);
        $this->postJson('/latihan/jawaban', ['idSoal' => 'lain', 'goresan' => $this->goresanBuruk()])->assertStatus(409);
    }

    /** @uji C16-UT10 | Normal | nilaiJawaban (mode non-kanvas: bunyi, merangkai, transliterasi) | Pilihan bunyi benar; urutan ubin benar; jawaban "Hana" untuk kunci "hana" | Semua benar, skor 100 */
    #[TestDox('C16-UT10 Normal: penilaian non-kanvas benar')]
    public function test_ut10_non_kanvas_benar(): void
    {
        $this->assertTrue(PenilaiJawaban::bunyi('p2', 'p2')->benar);
        $this->assertSame(100.0, PenilaiJawaban::merangkai(['ꦲ', 'ꦤ'], ['ꦲ', 'ꦤ'])->skor);
        $this->assertTrue(PenilaiJawaban::transliterasi(' Hana ', 'hana')->benar);
    }

    /** @uji C16-UT11 | Batas | nilaiJawaban (mode non-kanvas) | Merangkai 3 ubin dengan 1 posisi tertukar; urutan kosong vs kunci kosong | Skor 33.33 dengan posisiSalah [1,2]; urutan kosong skor 0 */
    #[TestDox('C16-UT11 Batas: merangkai sebagian benar dan urutan kosong')]
    public function test_ut11_non_kanvas_batas(): void
    {
        $h = PenilaiJawaban::merangkai(['ꦲ', 'ꦕ', 'ꦤ'], ['ꦲ', 'ꦤ', 'ꦕ']);

        $this->assertSame(33.33, $h->skor);
        $this->assertSame([1, 2], $h->posisiSalah);
        $this->assertSame(0.0, PenilaiJawaban::merangkai([], [])->skor);
    }

    /** @uji C16-UT12 | Negatif | nilaiJawaban (mode non-kanvas) | Bunyi tanpa pilihan (null), transliterasi kosong, merangkai kurang ubin | Semua salah */
    #[TestDox('C16-UT12 Negatif: jawaban non-kanvas kosong atau kurang salah')]
    public function test_ut12_non_kanvas_salah(): void
    {
        $this->assertFalse(PenilaiJawaban::bunyi(null, 'p1')->benar);
        $this->assertFalse(PenilaiJawaban::transliterasi('   ', '')->benar);
        $this->assertFalse(PenilaiJawaban::merangkai(['ꦲ'], ['ꦲ', 'ꦤ'])->benar);
    }

    /** @uji C16-UT13 | Normal | simpanHasil(hasil: HasilLatihan): RingkasanMotivasi | Jawaban benar lalu POST /latihan/selesai | Riwayat berhasil tersimpan; poin +10; streak 1; badge pemula */
    #[TestDox('C16-UT13 Normal: hasil berhasil menambah poin, streak, dan badge')]
    public function test_ut13_simpan_hasil(): void
    {
        $soal = $this->jawabBenar($this->pelajar);
        $this->postJson('/latihan/selesai', ['idSoal' => $soal->idSoal])->assertOk();
        $m = MotivasiBelajar::milikPelajar($this->pelajar->id_akun);

        $this->assertSame([10, 1, ['pemula']], [$m->poin, $m->streak, $m->badge()]);
    }

    /** @uji C16-UT14 | Batas | simpanHasil(hasil: HasilLatihan): RingkasanMotivasi | Soal yang sama diselesaikan dua kali | Riwayat bertambah tetapi poin tidak bertambah lagi (tetap 10) */
    #[TestDox('C16-UT14 Batas: soal yang pernah selesai tidak menambah poin')]
    public function test_ut14_simpan_ulang(): void
    {
        $layanan = app(SelesaikanLatihan::class);
        $materi = DB::table('materi_aksara')->value('id_materi');
        $hasil = ['id_materi' => $materi, 'target_soal' => 'ꦲ', 'mode' => 'bunyi', 'skor' => 100, 'status' => 'berhasil'];
        $layanan->selesaikan($this->pelajar->id_akun, [$hasil]);
        $kedua = $layanan->selesaikan($this->pelajar->id_akun, [$hasil]);

        $this->assertSame(0, $kedua->ambil('poinDidapat'));
        $this->assertSame(10, $kedua->ambil('totalPoin'));
    }

    /** @uji C16-UT15 | Negatif | simpanHasil(hasil: HasilLatihan): RingkasanMotivasi | (a) POST /latihan/selesai sebelum jawaban berhasil; (b) daftar hasil kosong; (c) skor 150 | (a) 422 BELUM_BERHASIL; (b) TANPA_HASIL; (c) SIMPAN_GAGAL dan transaksi dibatalkan */
    #[TestDox('C16-UT15 Negatif: hasil belum berhasil atau tidak valid tidak disimpan')]
    public function test_ut15_simpan_ditolak(): void
    {
        $soal = $this->bukaSoal($this->pelajar);
        $this->postJson('/latihan/selesai', ['idSoal' => $soal->idSoal])->assertStatus(422);
        $layanan = app(SelesaikanLatihan::class);

        $this->assertSame('TANPA_HASIL', $layanan->selesaikan($this->pelajar->id_akun, [])->kode);
        $materi = DB::table('materi_aksara')->value('id_materi');
        $this->assertSame('SIMPAN_GAGAL', $layanan->selesaikan($this->pelajar->id_akun, [['id_materi' => $materi, 'target_soal' => 'ꦲ', 'mode' => 'bunyi', 'skor' => 150, 'status' => 'berhasil']])->kode);
        $this->assertSame(0, DB::table('riwayat_latihan')->count());
    }

    /** @uji C16-UT16 | Normal | periksaBadge(streak, poin, dimiliki): List<String> | streak 7, poin 100, belum punya badge | [pemula, konsisten_7, seratus_poin] */
    #[TestDox('C16-UT16 Normal: semua syarat badge terpenuhi')]
    public function test_ut16_badge_semua(): void
    {
        $this->assertSame(['pemula', 'konsisten_7', 'seratus_poin'], (new PemeriksaBadge)->periksa(7, 100, []));
    }

    /** @uji C16-UT17 | Batas | periksaBadge(streak, poin, dimiliki): List<String> | streak 6, poin 9 (tepat di bawah ambang) dan poin 10 | Poin 9: []; poin 10: [pemula] */
    #[TestDox('C16-UT17 Batas: ambang badge tepat')]
    public function test_ut17_badge_ambang(): void
    {
        $this->assertSame([], (new PemeriksaBadge)->periksa(6, 9, []));
        $this->assertSame(['pemula'], (new PemeriksaBadge)->periksa(6, 10, []));
    }

    /** @uji C16-UT18 | Negatif | periksaBadge(streak, poin, dimiliki): List<String> | Syarat terpenuhi tetapi badge sudah dimiliki | Daftar kosong (tidak diberikan ulang) */
    #[TestDox('C16-UT18 Negatif: badge yang dimiliki tidak diberikan ulang')]
    public function test_ut18_badge_dimiliki(): void
    {
        $this->assertSame([], (new PemeriksaBadge)->periksa(7, 100, ['pemula', 'konsisten_7', 'seratus_poin']));
    }
}
