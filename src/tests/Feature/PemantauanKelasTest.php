<?php

namespace Tests\Feature;

use App\Console\Commands\ProfilProgresKelas;
use App\Http\Controllers\ProgresController;
use App\Support\Periode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/** Pengujian I-17 Pantau Progres Anggota (UC-08) dan agregasi kelas (Algo-102). */
class PemantauanKelasTest extends TestCase
{
    use BantuanData, RefreshDatabase;

    public function test_pengajar_lain_tidak_bisa_memantau_kelas_yang_bukan_miliknya(): void
    {
        $idKelas = $this->buatKelas($this->buatPengajar('Bu Rina'));

        $this->actingAs($this->buatPengajar('Pak Dimas'))->get('/pemantauan/'.$idKelas)->assertForbidden();
    }

    public function test_pelajar_tidak_bisa_membuka_pemantauan(): void
    {
        $idKelas = $this->buatKelas($this->buatPengajar());

        $this->actingAs($this->buatPelajar())->get('/pemantauan/'.$idKelas)->assertForbidden();
    }

    public function test_daftar_kelas_langsung_membuka_kelas_tunggal(): void
    {
        $pengajar = $this->buatPengajar();
        $idKelas = $this->buatKelas($pengajar);

        $this->actingAs($pengajar)->get('/pemantauan')->assertRedirect(route('pemantauan.index', $idKelas));
    }

    public function test_daftar_kelas_menampilkan_jumlah_anggota_aktif(): void
    {
        $pengajar = $this->buatPengajar();
        $kelasA = $this->buatKelas($pengajar, nama: 'Kelas A');
        $this->buatKelas($pengajar, nama: 'Kelas B');
        $this->gabung($kelasA, $this->buatPelajar());
        $this->gabung($kelasA, $this->buatPelajar(), 'keluar');

        $this->actingAs($pengajar)->get('/pemantauan')->assertOk()->assertSee('Kelas A')->assertSee('1 anggota aktif');
    }

    public function test_anggota_yang_keluar_tidak_ikut_dipantau(): void
    {
        $pengajar = $this->buatPengajar();
        $idKelas = $this->buatKelas($pengajar);
        $aktif = $this->buatPelajar('Anggota Aktif');
        $keluar = $this->buatPelajar('Anggota Keluar');
        $this->gabung($idKelas, $aktif);
        $this->gabung($idKelas, $keluar, 'keluar');
        $materi = $this->buatMateri();
        $this->riwayat($aktif, $materi, 80);
        $this->riwayat($keluar, $materi, 10, 'perlu_diulang');

        $this->actingAs($pengajar)->get('/pemantauan/'.$idKelas)
            ->assertOk()
            ->assertSee('Anggota Aktif')
            ->assertDontSee('Anggota Keluar');

        $ringkasan = app(ProgresController::class)->agregasiProgres([$aktif->id_akun], Periode::semua());
        $this->assertSame(80.0, $ringkasan['rataPenguasaan']);
    }

    public function test_detail_pengguna_yang_bukan_anggota_aktif_ditolak(): void
    {
        $pengajar = $this->buatPengajar();
        $idKelas = $this->buatKelas($pengajar);
        $bukanAnggota = $this->buatPelajar('Orang Lain');

        $this->actingAs($pengajar)->get('/pemantauan/'.$idKelas.'?pelajar='.$bukanAnggota->id_akun)
            ->assertOk()
            ->assertSee('bukan anggota aktif');
    }

    public function test_kelas_tanpa_riwayat_menampilkan_keadaan_kosong(): void
    {
        $pengajar = $this->buatPengajar();
        $idKelas = $this->buatKelas($pengajar);
        $this->gabung($idKelas, $this->buatPelajar());

        $this->actingAs($pengajar)->get('/pemantauan/'.$idKelas)->assertOk()->assertSee('Belum ada riwayat latihan anggota');
    }

    public function test_umpan_balik_tersimpan_untuk_anggota_aktif(): void
    {
        $pengajar = $this->buatPengajar();
        $idKelas = $this->buatKelas($pengajar);
        $sari = $this->buatPelajar('Sari');
        $this->gabung($idKelas, $sari);

        $this->actingAs($pengajar)
            ->post('/pemantauan/'.$idKelas.'/umpan-balik', ['id_pelajar' => $sari->id_akun, 'pesan' => 'Bagus, ulangi aksara na.'])
            ->assertRedirect(route('pemantauan.index', ['idKelas' => $idKelas, 'pelajar' => $sari->id_akun]));

        $feedback = DB::table('data_feedback')->sole();
        $this->assertSame($sari->id_akun, (int) $feedback->id_akun);
        $this->assertSame('materi', $feedback->kategori);
        $this->assertSame('baru', $feedback->status);
        $this->assertStringContainsString('Bagus, ulangi aksara na.', $feedback->pesan);
    }

    public function test_umpan_balik_ke_bukan_anggota_ditolak(): void
    {
        $pengajar = $this->buatPengajar();
        $idKelas = $this->buatKelas($pengajar);
        $orangLain = $this->buatPelajar();

        $this->actingAs($pengajar)
            ->post('/pemantauan/'.$idKelas.'/umpan-balik', ['id_pelajar' => $orangLain->id_akun, 'pesan' => 'Halo'])
            ->assertSessionHasErrors('pesan');
        $this->assertSame(0, DB::table('data_feedback')->count());
    }

    public function test_catatan_pengumpulan_tanpa_materi_tidak_merusak_agregasi(): void
    {
        $pengajar = $this->buatPengajar();
        $idKelas = $this->buatKelas($pengajar);
        $sari = $this->buatPelajar();
        $this->gabung($idKelas, $sari);
        $this->riwayat($sari, $this->buatMateri(), 70);
        // baris seperti yang ditulis modul tugas saat pengumpulan
        DB::table('riwayat_latihan')->insert(['id_pelajar' => $sari->id_akun, 'id_materi' => null, 'mode_aktivitas' => 'pengumpulan',
            'status' => 'selesai', 'tanggal_pengerjaan' => now(), 'waktu_selesai' => now()]);

        $ringkasan = app(ProgresController::class)->agregasiProgres([$sari->id_akun], Periode::semua());

        $this->assertSame(1, $ringkasan['jumlahLatihan']);
        $this->assertCount(1, $ringkasan['penguasaanMateri']);
        $this->actingAs($pengajar)->get('/pemantauan/'.$idKelas)->assertOk();
    }

    public function test_agregasi_dioptimalkan_sama_dengan_versi_dppl(): void
    {
        $materi = [$this->buatMateri('A'), $this->buatMateri('B'), $this->buatMateri('C')];
        $anggota = [];
        mt_srand(7);
        for ($i = 0; $i < 6; $i++) {
            $p = $this->buatPelajar('P'.$i);
            $anggota[] = $p->id_akun;
            for ($j = 0; $j < 15; $j++) {
                $skor = mt_rand(20, 100);
                $this->riwayat($p, $materi[mt_rand(0, 2)], $skor, $skor >= 70 ? 'berhasil' : 'perlu_diulang', now()->subDays(mt_rand(0, 60))->toDateTimeString());
            }
        }
        $periode = Periode::hariTerakhir(30);

        $optimal = app(ProgresController::class)->agregasiProgres($anggota, $periode);
        $dppl = (function () use ($anggota, $periode) {
            return $this->agregasiVersiDppl($anggota, $periode);
        })->call(new ProfilProgresKelas);

        $this->assertSame($dppl['jumlahLatihan'], $optimal['jumlahLatihan']);
        $this->assertEquals($dppl['penguasaanMateri'], $optimal['penguasaanMateri']);
        $this->assertEquals($dppl['perPelajar'], $optimal['perPelajar']);
    }
}
