<?php

namespace Tests\Feature\Latihan;

use App\Models\AkunPengguna;
use App\Models\MotivasiBelajar;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Tests\Support\AlurLatihan;
use Database\Seeders\MateriSeeder;
use Tests\Feature\BantuanData;
use Tests\TestCase;

class SimpanHasilTest extends TestCase
{
    use AlurLatihan, BantuanData, RefreshDatabase;

    private AkunPengguna $pelajar;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(MateriSeeder::class);
        $this->pelajar = $this->buatPelajar();
    }

    private function selesaikan(string $idSoal)
    {
        return $this->postJson('/latihan/selesai', ['idSoal' => $idSoal]);
    }

    private function motivasi(): ?MotivasiBelajar
    {
        return MotivasiBelajar::find($this->pelajar->id_akun);
    }

    public function test_selesai_pertama_memberi_poin_streak_dan_badge(): void
    {
        $soal = $this->jawabBenar($this->pelajar);

        $this->selesaikan($soal->idSoal)->assertOk()->assertJson(['ok' => true, 'redirect' => route('latihan.hasil')]);

        $m = $this->motivasi();
        $this->assertSame([10, 1, ['pemula']], [$m->poin, $m->streak, $m->badge()]);
        $this->assertDatabaseHas('riwayat_latihan', ['target_soal' => $soal->targetSoal, 'status' => 'berhasil', 'id_tugas' => null]);
        $ringkasan = session('latihan.ringkasanTerakhir')['ringkasan'];
        $this->assertTrue($ringkasan['poinBertambah']);
        $this->assertSame(['pemula'], $ringkasan['badgeBaru']);
        $this->assertEquals(json_decode(json_encode(session('latihan')), true), session('latihan'));

        $this->get('/latihan/hasil')->assertOk()
            ->assertSeeInOrder(['Latihan rampung!', '+10', 'hari', 'Pemula Aksara', 'Menyelesaikan latihan pertama.'])
            ->assertDontSee('Soal ini sudah pernah kamu selesaikan.');
    }

    public function test_soal_yang_sama_lagi_tidak_menambah_poin(): void
    {
        $soal = $this->jawabBenar($this->pelajar);
        $this->selesaikan($soal->idSoal)->assertOk();

        // Ulangi sampai aksara yang sama muncul lagi.
        do {
            $this->post("/latihan/1/menulis/ulangi");
            $lagi = $this->jawabBenar($this->pelajar);
        } while ($lagi->targetSoal !== $soal->targetSoal);
        $this->selesaikan($lagi->idSoal)->assertOk();

        $this->assertSame(10, (int) $this->motivasi()->poin);
        $this->assertSame(2, DB::table('riwayat_latihan')->where('target_soal', $soal->targetSoal)->where('status', 'berhasil')->count());
        $this->assertFalse(session('latihan.ringkasanTerakhir')['ringkasan']['poinBertambah']);
        $this->get('/latihan/hasil')->assertSee('+0')->assertSee('Soal ini sudah pernah kamu selesaikan.');
    }

    public function test_simpan_dua_kali_ditolak(): void
    {
        $soal = $this->jawabBenar($this->pelajar);
        $this->selesaikan($soal->idSoal)->assertOk();

        $this->selesaikan($soal->idSoal)->assertStatus(422)->assertJson(['message' => 'Latihan belum berhasil diselesaikan']);
        $this->assertSame(1, DB::table('riwayat_latihan')->count());
    }

    public function test_simpan_tanpa_hasil_berhasil_ditolak(): void
    {
        $soal = $this->bukaSoal($this->pelajar);
        $this->selesaikan($soal->idSoal)->assertStatus(422);

        $this->postJson('/latihan/jawaban', ['idSoal' => $soal->idSoal, 'goresan' => $this->goresanBuruk()]);
        $this->selesaikan($soal->idSoal)->assertStatus(422)->assertJson(['message' => 'Latihan belum berhasil diselesaikan']);
        $this->assertSame(0, DB::table('riwayat_latihan')->where('status', 'berhasil')->count());
    }

    public static function kasusStreak(): array
    {
        return [
            'aktif kemarin' => ['2026-11-10 10:00', '2026-11-09', 4, 5],
            'aktif hari ini' => ['2026-11-10 10:00', '2026-11-10', 4, 4],
            'aktif tiga hari lalu' => ['2026-11-10 10:00', '2026-11-07', 4, 1],
            'pukul 23.30 WIB, aktif kemarin' => ['2026-11-10 23:30', '2026-11-09', 2, 3],
            'pukul 00.30 WIB, aktif kemarin' => ['2026-11-10 00:30', '2026-11-09', 2, 3],
            'pukul 00.30 WIB, aktif hari ini' => ['2026-11-10 00:30', '2026-11-10', 2, 2],
        ];
    }

    #[DataProvider('kasusStreak')]
    public function test_streak(string $sekarang, string $aktifTerakhir, int $streakAwal, int $streakAkhir): void
    {
        Carbon::setTestNow(Carbon::parse($sekarang, 'Asia/Jakarta'));
        DB::table('motivasi_belajar')->insert([
            'id_pelajar' => $this->pelajar->id_akun, 'streak' => $streakAwal, 'poin' => 10,
            'tanggal_aktif_terakhir' => $aktifTerakhir, 'daftar_badge' => '["pemula"]',
        ]);

        $this->selesaikan($this->jawabBenar($this->pelajar)->idSoal)->assertOk();

        $this->assertSame($streakAkhir, (int) $this->motivasi()->streak);
        $this->assertSame(Carbon::parse($sekarang, 'Asia/Jakarta')->toDateString(), $this->motivasi()->tanggal_aktif_terakhir->toDateString());
    }

    public function test_streak_tujuh_hari_memberi_badge_rajin(): void
    {
        DB::table('motivasi_belajar')->insert([
            'id_pelajar' => $this->pelajar->id_akun, 'streak' => 6, 'poin' => 30,
            'tanggal_aktif_terakhir' => today()->subDay()->toDateString(), 'daftar_badge' => '["pemula"]',
        ]);

        $this->selesaikan($this->jawabBenar($this->pelajar)->idSoal)->assertOk();

        $this->assertSame(['pemula', 'konsisten_7'], $this->motivasi()->badge());
    }

    public function test_gagal_di_tengah_transaksi_dibatalkan(): void
    {
        $soal = $this->jawabBenar($this->pelajar);
        // Galat buatan saat baris motivasi disimpan, setelah riwayat berhasil ditulis di transaksi yang sama.
        MotivasiBelajar::saving(fn () => throw new RuntimeException('basis data mati'));
        Log::spy();

        $this->selesaikan($soal->idSoal)->assertStatus(500)->assertJson(['message' => 'Hasil belum tersimpan. Coba lagi.']);

        $this->assertSame(0, DB::table('riwayat_latihan')->where('status', 'berhasil')->count());
        $this->assertNull($this->motivasi());
        Log::shouldHaveReceived('error')->withArgs(fn ($pesan, $konteks) => $pesan === 'Gagal menyimpan hasil latihan' && $konteks['galat'] === 'basis data mati');
        $this->assertNotNull(session('latihan.hasilTerakhir'));
    }

    public function test_lanjut_ke_soal_berikutnya_lalu_kembali_setelah_semua_selesai(): void
    {
        $dikerjakan = [];
        for ($i = 0; $i < 5; $i++) {
            $soal = $this->jawabBenar($this->pelajar);
            $this->assertNotContains($soal->targetSoal, $dikerjakan);
            $dikerjakan[] = $soal->targetSoal;
            $this->selesaikan($soal->idSoal)->assertOk();
            $tujuan = $i < 4 ? route('latihan.soal', [1, 'menulis']) : route('materi.show', 1);
            $this->post('/latihan/1/menulis/lanjut')->assertRedirect($tujuan);
        }
    }

    public function test_hasil_tanpa_ringkasan_dialihkan(): void
    {
        $this->actingAs($this->pelajar)->get('/latihan/hasil')->assertRedirect(route('beranda'));
    }
}
