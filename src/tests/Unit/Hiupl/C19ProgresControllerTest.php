<?php

namespace Tests\Unit\Hiupl;

use App\Http\Controllers\ProgresController;
use App\Models\MotivasiBelajar;
use App\Support\Periode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\Feature\BantuanData;
use Tests\TestCase;

class C19ProgresControllerTest extends TestCase
{
    use BantuanData, BantuanMateri, RefreshDatabase;

    private function k(): ProgresController
    {
        return app(ProgresController::class);
    }

    #[TestDox('C19-UT01 Normal: ringkasan progres Pelajar')]
    public function test_ut01_hitung_progres(): void
    {
        $p = $this->buatPelajar();
        $m = $this->buatMateri();
        $this->riwayat($p, $m, 80);
        $this->riwayat($p, $m, 60, 'perlu_diulang', null, 'ꦤ');
        $r = $this->k()->hitungProgres($p->id_akun);

        $this->assertSame(70.0, $r['penguasaan'][0]['nilai']);
        $this->assertSame([2, ['ꦤ' => 1], false], [$r['jumlahLatihan'], $r['polaKesalahan'], $r['kosong']]);
    }

    #[TestDox('C19-UT02 Batas: filter periode mengecualikan riwayat lama')]
    public function test_ut02_periode(): void
    {
        $p = $this->buatPelajar();
        $this->riwayat($p, $this->buatMateri(), 90, 'berhasil', now()->subDays(10)->toDateTimeString());

        $this->assertTrue($this->k()->hitungProgres($p->id_akun, Periode::hariTerakhir(7))['kosong']);
        $this->assertFalse($this->k()->hitungProgres($p->id_akun, Periode::semua())['kosong']);
    }

    #[TestDox('C19-UT03 Negatif: tanpa riwayat menghasilkan ringkasan kosong')]
    public function test_ut03_tanpa_riwayat(): void
    {
        $r = $this->k()->hitungProgres($this->buatPelajar()->id_akun);

        $this->assertTrue($r['kosong']);
        $this->assertSame([], $r['penguasaan']);
    }

    #[TestDox('C19-UT04 Normal: rekomendasi dari penguasaan')]
    public function test_ut04_rekomendasi(): void
    {
        $p = $this->buatPelajar();
        $m = $this->materiLengkap('T1');
        $this->riwayat($p, $m, 50, 'perlu_diulang');
        $r = $this->k()->siapkanRekomendasi($p->id_akun);

        $this->assertSame($m->id_materi, $r[0]['materi']->id_materi);
        $this->assertSame(50.0, $r[0]['penguasaan']);
    }

    #[TestDox('C19-UT05 Batas: Pelajar baru direkomendasikan tingkat awal')]
    public function test_ut05_rekomendasi_baru(): void
    {
        $this->materiLengkap('T1');
        $this->materiLengkap('T2', 2);
        $r = $this->k()->siapkanRekomendasi($this->buatPelajar()->id_akun);

        $this->assertSame(['T1'], array_map(fn ($x) => $x['materi']->nama_materi, $r));
        $this->assertNull($r[0]['penguasaan']);
    }

    #[TestDox('C19-UT06 Negatif: tanpa materi tidak ada rekomendasi')]
    public function test_ut06_rekomendasi_tanpa_materi(): void
    {
        $this->assertSame([], $this->k()->siapkanRekomendasi($this->buatPelajar()->id_akun));
    }

    #[TestDox('C19-UT07 Normal: anggota melihat papan peringkat')]
    public function test_ut07_scoreboard(): void
    {
        $p = $this->buatPelajar();
        $k = $this->buatKelas($this->buatPengajar(), true);
        $this->gabung($k, $p);
        MotivasiBelajar::milikPelajar($p->id_akun)->tambahPoin(1, 20);
        $this->actingAs($p);
        $s = $this->k()->ambilScoreboard($k);

        $this->assertTrue($s['aktif']);
        $this->assertSame(20, $s['daftar'][0]['poin']);
    }

    public function test_ut08_scoreboard_batas(): void
    {
        $g = $this->buatPengajar();
        $p = $this->buatPelajar();
        $aktif = $this->buatKelas($g, true);
        $mati = $this->buatKelas($g, false, 'Mati');
        $this->gabung($mati, $p);

        $this->actingAs($g);
        $this->assertTrue($this->k()->ambilScoreboard($aktif)['aktif']);
        $this->actingAs($p);
        $this->assertStringContainsString('belum diaktifkan', $this->k()->ambilScoreboard($mati)['pesan']);
    }

    #[TestDox('C19-UT09 Negatif: bukan anggota atau kelas tidak ada ditolak')]
    public function test_ut09_scoreboard_ditolak(): void
    {
        $k = $this->buatKelas($this->buatPengajar(), true);
        $this->actingAs($this->buatPelajar());

        $this->assertStringContainsString('bukan anggota aktif', $this->k()->ambilScoreboard($k)['pesan']);
        $this->assertSame([], $this->k()->ambilScoreboard(999999)['daftar']);
    }

    #[TestDox('C19-UT10 Normal: agregasi progres kelas')]
    public function test_ut10_agregasi(): void
    {
        $a = $this->buatPelajar('A');
        $b = $this->buatPelajar('B');
        $m = $this->buatMateri();
        $this->riwayat($a, $m, 80);
        $this->riwayat($b, $m, 60, 'perlu_diulang');
        $r = $this->k()->agregasiProgres([$a->id_akun, $b->id_akun], Periode::semua());

        $this->assertSame([2, 70.0, false], [$r['jumlahLatihan'], $r['rataPenguasaan'], $r['kosong']]);
    }

    public function test_ut11_agregasi_tanpa_riwayat(): void
    {
        $r = $this->k()->agregasiProgres([$this->buatPelajar()->id_akun], Periode::hariTerakhir(7));

        $this->assertSame([1, null, true], [$r['jumlahAnggota'], $r['rataPenguasaan'], $r['kosong']]);
    }

    #[TestDox('C19-UT12 Negatif: tanpa anggota tidak menghitung riwayat orang lain')]
    public function test_ut12_agregasi_kosong(): void
    {
        $this->riwayat($this->buatPelajar(), $this->buatMateri(), 90);
        $r = $this->k()->agregasiProgres([], Periode::semua());

        $this->assertSame([0, 0, true], [$r['jumlahAnggota'], $r['jumlahLatihan'], $r['kosong']]);
    }

    #[TestDox('C19-UT13 Normal: indikator motivasi menandai badge yang dimiliki')]
    public function test_ut13_indikator(): void
    {
        $p = $this->buatPelajar();
        MotivasiBelajar::milikPelajar($p->id_akun)->berikanBadge('pemula');
        $badge = collect($this->k()->ambilIndikatorMotivasi($p->id_akun)['badge'])->pluck('dimiliki', 'kode')->all();

        $this->assertTrue($badge['pemula']);
        $this->assertFalse($badge['seratus_poin']);
    }

    #[TestDox('C19-UT14 Negatif: Pelajar tanpa motivasi bernilai nol')]
    public function test_ut14_indikator_kosong(): void
    {
        $d = $this->k()->ambilIndikatorMotivasi($this->buatPelajar()->id_akun);

        $this->assertSame([0, 0], [$d['streak'], $d['poin']]);
        $this->assertNotContains(true, array_column($d['badge'], 'dimiliki'));
    }

    #[TestDox('C19-UT15 Batas: seluruh badge dimiliki')]
    public function test_ut15_indikator_penuh(): void
    {
        $p = $this->buatPelajar();
        $m = MotivasiBelajar::milikPelajar($p->id_akun);
        foreach (['pemula', 'konsisten_7', 'seratus_poin'] as $b) {
            $m->berikanBadge($b);
        }

        $this->assertNotContains(false, array_column($this->k()->ambilIndikatorMotivasi($p->id_akun)['badge'], 'dimiliki'));
    }

    #[TestDox('C19-UT16 Normal: riwayat dan detail hasil per materi')]
    public function test_ut16_riwayat_detail(): void
    {
        $p = $this->buatPelajar();
        $a = $this->buatMateri('A');
        $this->riwayat($p, $a, 80);
        $this->riwayat($p, $a, 50, 'perlu_diulang');
        $this->riwayat($p, $this->buatMateri('B'), 90);

        $this->assertCount(3, $this->k()->ambilRiwayat($p->id_akun, Periode::semua()));
        $this->assertCount(2, $this->k()->ambilDetailHasil($p->id_akun, $a->id_materi));
    }

    #[TestDox('C19-UT17 Batas: periode menyaring riwayat dan pola kesalahan')]
    public function test_ut17_periode_pola(): void
    {
        $p = $this->buatPelajar();
        $this->riwayat($p, $this->buatMateri(), 20, 'perlu_diulang', now()->subDays(10)->toDateTimeString(), 'ꦤ');

        $this->assertCount(0, $this->k()->ambilRiwayat($p->id_akun, Periode::hariTerakhir(7)));
        $this->assertSame([], $this->k()->hitungPolaKesalahan($p->id_akun, Periode::hariTerakhir(7)));
        $this->assertSame(['ꦤ' => 1], $this->k()->hitungPolaKesalahan($p->id_akun));
    }

    #[TestDox('C19-UT18 Negatif: detail dan pola kosong')]
    public function test_ut18_detail_pola_kosong(): void
    {
        $p = $this->buatPelajar();
        $this->riwayat($p, $this->buatMateri(), 100);

        $this->assertCount(0, $this->k()->ambilDetailHasil($p->id_akun, $this->buatMateri('Lain')->id_materi));
        $this->assertSame([], $this->k()->hitungPolaKesalahan($p->id_akun));
    }
}
