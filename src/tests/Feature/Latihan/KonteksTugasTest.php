<?php

namespace Tests\Feature\Latihan;

use App\Models\AkunPengguna;
use Database\Seeders\MateriSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Feature\BantuanData;
use Tests\Support\AlurLatihan;
use Tests\TestCase;

class KonteksTugasTest extends TestCase
{
    use AlurLatihan, BantuanData, RefreshDatabase;

    private AkunPengguna $pelajar;

    private int $tugas;

    private int $komponen;

    private int $komponenTugasLain;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(MateriSeeder::class);
        $this->pelajar = $this->buatPelajar();
        $kelas = $this->buatKelas($this->buatPengajar());
        $this->gabung($kelas, $this->pelajar);
        $this->tugas = $this->buatTugas($kelas);
        $this->komponen = $this->buatKomponen($this->tugas);
        $this->komponenTugasLain = $this->buatKomponen($this->buatTugas($kelas));
    }

    private function buatTugas(int $kelas): int
    {
        return DB::table('tugas_kelas')->insertGetId(
            ['id_kelas' => $kelas, 'instruksi' => 'Latihan menulis', 'tenggat' => now()->addDay(), 'status' => 'terbit'],
            'id_tugas',
        );
    }

    private function buatKomponen(int $tugas): int
    {
        return DB::table('komponen_tugas')->insertGetId(
            ['id_tugas' => $tugas, 'jenis' => 'latihan', 'id_materi' => 1, 'mode' => 'menulis', 'wajib' => true],
            'id_komponen',
        );
    }

    private function url(?int $komponen = null): string
    {
        return '/latihan/1/menulis?tugas='.$this->tugas.'&komponen='.($komponen ?? $this->komponen);
    }

    public function test_komponen_milik_tugas_lain_ditolak(): void
    {
        $this->actingAs($this->pelajar)->get($this->url($this->komponenTugasLain))
            ->assertForbidden()->assertSee('Kamu tidak memiliki akses ke tugas ini.');
    }

    public function test_bukan_anggota_aktif_ditolak(): void
    {
        DB::table('keanggotaan_kelas')->update(['status' => 'keluar']);

        $this->actingAs($this->pelajar)->get($this->url())->assertForbidden();
        $this->actingAs($this->buatPelajar('Budi'))->get($this->url())->assertForbidden();
    }

    public function test_materi_atau_mode_tidak_cocok_ditolak(): void
    {
        DB::table('komponen_tugas')->where('id_komponen', $this->komponen)->update(['mode' => 'bunyi']);

        $this->actingAs($this->pelajar)->get($this->url())->assertForbidden();
    }

    public function test_tenggat_lewat_tetap_bisa_dikerjakan(): void
    {
        DB::table('tugas_kelas')->where('id_tugas', $this->tugas)->update(['tenggat' => now()->subDays(2)]);

        $this->actingAs($this->pelajar)->get($this->url())->assertOk();
    }

    public function test_hasil_berhasil_memuat_tugas_dan_komponen(): void
    {
        $soal = $this->jawabBenar($this->pelajar, $this->url());
        $this->postJson('/latihan/selesai', ['idSoal' => $soal->idSoal])->assertOk();

        $baris = DB::table('riwayat_latihan')->where('status', 'berhasil')->first();
        $this->assertSame([$this->tugas, $this->komponen], [(int) $baris->id_tugas, (int) $baris->id_komponen]);
        $this->assertNotNull($baris->waktu_selesai);
        $this->get('/latihan/hasil')->assertSee('Kembali ke tugas')->assertDontSee('Lanjut');
    }

    public function test_hasil_perlu_diulang_tanpa_tugas_dan_komponen(): void
    {
        $soal = $this->bukaSoal($this->pelajar, $this->url());
        $this->postJson('/latihan/jawaban', ['idSoal' => $soal->idSoal, 'goresan' => $this->goresanBuruk()])
            ->assertJson(['status' => 'perlu_diulang']);

        $this->assertDatabaseHas('riwayat_latihan', ['status' => 'perlu_diulang', 'id_tugas' => null, 'id_komponen' => null, 'waktu_selesai' => null]);
    }

    public function test_soal_tanpa_konteks_tidak_memakai_soal_tugas(): void
    {
        $dariTugas = $this->bukaSoal($this->pelajar, $this->url());

        $this->assertNotSame($dariTugas->idSoal, $this->bukaSoal($this->pelajar)->idSoal);
        $this->assertNull(session('latihan.soalAktif')['konteksTugas']);
    }
}
