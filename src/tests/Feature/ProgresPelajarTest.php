<?php

namespace Tests\Feature;

use App\Http\Controllers\ProgresController;
use App\Models\MotivasiBelajar;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Pengujian halaman I-11 Progres Pelajar (UC-04, C14 dan C19).
 */
class ProgresPelajarTest extends TestCase
{
    use BantuanData, RefreshDatabase;

    public function test_tamu_diarahkan_dan_tidak_bisa_membuka_halaman_progres(): void
    {
        $this->get('/progres')->assertRedirect();
        $this->assertGuest();
    }

    public function test_pengajar_tidak_bisa_membuka_halaman_progres_pelajar(): void
    {
        $this->actingAs($this->buatPengajar())->get('/progres')->assertForbidden();
    }

    public function test_pelajar_baru_melihat_keadaan_kosong_dan_materi_awal(): void
    {
        $this->buatMateri('Nglegena 1', 'terbit', 1);
        $this->buatMateri('Pasangan', 'draf', 2);

        $this->actingAs($this->buatPelajar())->get('/progres')
            ->assertOk()
            ->assertSee('Belum ada riwayat latihan')
            ->assertSee('Nglegena 1')
            ->assertDontSee('Pasangan'); // materi draf tidak direkomendasikan
    }

    public function test_pelajar_melihat_penguasaan_dan_riwayatnya_sendiri_saja(): void
    {
        $sari = $this->buatPelajar('Sari');
        $budi = $this->buatPelajar('Budi');
        $materi = $this->buatMateri('Nglegena 1');
        // draf agar tidak muncul sebagai rekomendasi; yang diuji adalah riwayat Budi tidak bocor ke Sari
        $materiLain = $this->buatMateri('Materi Milik Budi', 'draf', 3);
        $this->riwayat($sari, $materi, 80);
        $this->riwayat($budi, $materiLain, 50);

        $this->actingAs($sari)->get('/progres')
            ->assertOk()
            ->assertSee('Nglegena 1')
            ->assertSee('80%')
            ->assertDontSee('Materi Milik Budi');
    }

    public function test_rekomendasi_melewati_materi_tuntas_dan_memilih_yang_belum(): void
    {
        $sari = $this->buatPelajar();
        $mudah = $this->buatMateri('Materi Mudah');
        $sulit = $this->buatMateri('Materi Sulit');
        $this->riwayat($sari, $mudah, 95);
        $this->riwayat($sari, $sulit, 40, 'perlu_diulang');

        $rekomendasi = app(ProgresController::class)->siapkanRekomendasi($sari->id_akun);

        $this->assertSame('Materi Sulit', $rekomendasi[0]['materi']->nama_materi);
        $this->assertEquals(40.0, $rekomendasi[0]['penguasaan']);
        $this->assertNotContains('Materi Mudah', array_map(fn ($r) => $r['materi']->nama_materi, $rekomendasi));
    }

    public function test_papan_peringkat_tampil_bila_diaktifkan_pengajar(): void
    {
        $idKelas = $this->buatKelas($this->buatPengajar(), scoreboard: true);
        $sari = $this->buatPelajar('Sari');
        $budi = $this->buatPelajar('Budi');
        $this->gabung($idKelas, $sari);
        $this->gabung($idKelas, $budi);
        MotivasiBelajar::milikPelajar($budi->id_akun)->tambahPoin(1, 40);

        $this->actingAs($sari)->get('/progres')->assertOk()->assertSee('Papan peringkat')->assertSee('40 poin');
    }

    public function test_papan_peringkat_tidak_tampil_bila_nonaktif(): void
    {
        $idKelas = $this->buatKelas($this->buatPengajar(), scoreboard: false);
        $sari = $this->buatPelajar();
        $this->gabung($idKelas, $sari);

        $this->actingAs($sari)->get('/progres')->assertOk()->assertSee('belum diaktifkan oleh Pengajar');
    }

    public function test_papan_peringkat_kelas_lain_ditolak(): void
    {
        $idKelas = $this->buatKelas($this->buatPengajar());
        $sari = $this->buatPelajar();

        $this->actingAs($sari);
        $hasil = app(ProgresController::class)->ambilScoreboard($idKelas);

        $this->assertFalse($hasil['aktif']);
        $this->assertSame([], $hasil['daftar']);
    }

    public function test_detail_hasil_materi_tampil_saat_dipilih(): void
    {
        $sari = $this->buatPelajar();
        $materi = $this->buatMateri('Nglegena 1');
        $this->riwayat($sari, $materi, 55, 'perlu_diulang', null, 'ꦕ');

        $this->actingAs($sari)->get('/progres?materi='.$materi->id_materi)
            ->assertOk()->assertSee('Detail hasil: Nglegena 1')->assertSee('Perlu diulang');
    }

    public function test_menu_progres_muncul_di_layout_untuk_pelajar(): void
    {
        $this->actingAs($this->buatPelajar())->get('/progres')->assertOk()->assertSee('href="'.route('progres.index').'"', false);
    }
}
