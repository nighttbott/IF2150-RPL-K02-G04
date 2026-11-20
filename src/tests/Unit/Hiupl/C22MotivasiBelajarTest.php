<?php

namespace Tests\Unit\Hiupl;

use App\Models\MotivasiBelajar;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\Feature\BantuanData;
use Tests\TestCase;


class C22MotivasiBelajarTest extends TestCase
{
    use BantuanData, RefreshDatabase;

    protected function tearDown(): void
    {
        CarbonImmutable::setTestNow();
        parent::tearDown();
    }

    private function motivasi(): MotivasiBelajar
    {
        return MotivasiBelajar::milikPelajar($this->buatPelajar()->id_akun);
    }

    #[TestDox('C22-UT01 Normal: tiga hari berturut-turut menghasilkan streak 3')]
    public function test_ut01_streak_berturut(): void
    {
        $m = $this->motivasi();
        foreach (['2026-11-01', '2026-11-02', '2026-11-03'] as $t) {
            $m->perbaruiStreak($t);
        }

        $this->assertSame(3, $m->fresh()->streak);
    }

    #[TestDox('C22-UT02 Batas: hari sama tidak menambah streak, pergantian tengah malam menambah')]
    public function test_ut02_streak_hari_sama_dan_tengah_malam(): void
    {
        $m = $this->motivasi();
        $m->perbaruiStreak('2026-11-01 08:00:00');
        $m->perbaruiStreak('2026-11-01 23:59:00');
        $this->assertSame(1, $m->fresh()->streak);

        $m->perbaruiStreak('2026-11-02 00:00:00');
        $this->assertSame(2, $m->fresh()->streak);
    }

    #[TestDox('C22-UT03 Negatif: hari terlewat mengembalikan streak ke 1')]
    public function test_ut03_streak_terputus(): void
    {
        $m = $this->motivasi();
        $m->perbaruiStreak('2026-11-01');
        $m->perbaruiStreak('2026-11-02');
        $m->perbaruiStreak('2026-11-04');

        $this->assertSame(1, $m->fresh()->streak);
    }

    #[TestDox('C22-UT04 Normal: poin bertambah dan total dikembalikan')]
    public function test_ut04_tambah_poin(): void
    {
        $m = $this->motivasi();
        $m->tambahPoin(1, 10);
        $hasil = $m->tambahPoin(2, 10);

        $this->assertTrue($hasil->berhasil);
        $this->assertSame(20, $hasil->ambil('poin'));
    }

    #[TestDox('C22-UT05 Batas: nilai poin 1 diterima')]
    public function test_ut05_tambah_poin_satu(): void
    {
        $m = $this->motivasi();

        $this->assertTrue($m->tambahPoin(1, 1)->berhasil);
        $this->assertSame(1, $m->fresh()->poin);
    }

    #[TestDox('C22-UT06 Negatif: poin nol atau negatif ditolak')]
    public function test_ut06_tambah_poin_tidak_valid(): void
    {
        $m = $this->motivasi();

        $this->assertSame('NILAI_TIDAK_VALID', $m->tambahPoin(1, 0)->kode);
        $this->assertSame('NILAI_TIDAK_VALID', $m->tambahPoin(1, -5)->kode);
        $this->assertSame(0, $m->fresh()->poin);
    }

    #[TestDox('C22-UT07 Normal: badge baru tersimpan')]
    public function test_ut07_badge_baru(): void
    {
        $m = $this->motivasi();

        $this->assertTrue($m->berikanBadge('pemula')->ambil('baru'));
        $this->assertSame(['pemula'], $m->fresh()->badge());
    }

    #[TestDox('C22-UT08 Batas: badge kedua ditambahkan tanpa menghapus yang lama')]
    public function test_ut08_badge_kedua(): void
    {
        $m = $this->motivasi();
        $m->berikanBadge('pemula');
        $m->berikanBadge('konsisten_7');

        $this->assertSame(['pemula', 'konsisten_7'], $m->fresh()->badge());
    }

    #[TestDox('C22-UT09 Negatif: badge yang sudah dimiliki tidak digandakan')]
    public function test_ut09_badge_ganda(): void
    {
        $m = $this->motivasi();
        $m->berikanBadge('pemula');

        $this->assertFalse($m->berikanBadge('pemula')->ambil('baru'));
        $this->assertSame(['pemula'], $m->fresh()->badge());
    }

    #[TestDox('C22-UT10 Normal: papan peringkat urut poin menurun')]
    public function test_ut10_scoreboard_urut(): void
    {
        $peta = [];
        foreach (['A' => 50, 'B' => 30, 'C' => 10] as $nama => $poin) {
            $p = $this->buatPelajar($nama);
            MotivasiBelajar::milikPelajar($p->id_akun)->tambahPoin(1, $poin);
            $peta[$p->id_akun] = $nama;
        }
        $papan = MotivasiBelajar::susunScoreboard($peta);

        $this->assertSame([1, 2, 3], array_column($papan, 'peringkat'));
        $this->assertSame(['A', 'B', 'C'], array_column($papan, 'nama_tampilan'));
    }

    #[TestDox('C22-UT11 Batas: poin sama berperingkat sama')]
    public function test_ut11_scoreboard_seri(): void
    {
        $a = $this->buatPelajar('A');
        $b = $this->buatPelajar('B');
        $c = $this->buatPelajar('C');
        MotivasiBelajar::milikPelajar($a->id_akun)->tambahPoin(1, 30);
        MotivasiBelajar::milikPelajar($b->id_akun)->tambahPoin(1, 30);
        $papan = MotivasiBelajar::susunScoreboard([$a->id_akun => 'A', $b->id_akun => 'B', $c->id_akun => 'C']);

        $this->assertSame([1, 1, 3], array_column($papan, 'peringkat'));
        $this->assertSame(0, $papan[2]['poin']);
        $this->assertArrayNotHasKey('email', $papan[0]);
    }

    #[TestDox('C22-UT12 Negatif: tanpa anggota menghasilkan papan kosong')]
    public function test_ut12_scoreboard_kosong(): void
    {
        $this->assertSame([], MotivasiBelajar::susunScoreboard([]));
    }

    #[TestDox('C22-UT13 Normal: ambilMotivasi mengembalikan streak, poin, badge')]
    public function test_ut13_ambil_motivasi(): void
    {
        $m = $this->motivasi();
        $m->perbaruiStreak(today());
        $m->tambahPoin(1, 40);
        $m->berikanBadge('pemula');
        $data = MotivasiBelajar::ambilMotivasi($m->id_pelajar);

        $this->assertSame([1, 40, ['pemula']], [$data['streak'], $data['poin'], $data['daftarBadge']]);
    }

    #[TestDox('C22-UT14 Batas: streak terputus ditampilkan nol')]
    public function test_ut14_streak_tampil(): void
    {
        $m = $this->motivasi();
        $m->perbaruiStreak(CarbonImmutable::today()->subDay());
        $this->assertSame(1, MotivasiBelajar::ambilMotivasi($m->id_pelajar)['streak']);

        $m2 = $this->motivasi();
        $m2->perbaruiStreak(CarbonImmutable::today()->subDays(3));
        $this->assertSame(0, MotivasiBelajar::ambilMotivasi($m2->id_pelajar)['streak']);
        $this->assertSame(1, $m2->fresh()->streak);
    }

    #[TestDox('C22-UT15 Negatif: Pelajar tanpa data motivasi dianggap nol')]
    public function test_ut15_motivasi_tidak_ada(): void
    {
        $data = MotivasiBelajar::ambilMotivasi(999999);

        $this->assertSame([0, 0, []], [$data['streak'], $data['poin'], $data['daftarBadge']]);
    }

    #[TestDox('C22-UT16 Normal: poin kelas urut menurun')]
    public function test_ut16_poin_kelas(): void
    {
        $id = [];
        foreach ([10, 40, 20] as $poin) {
            $p = $this->buatPelajar('P'.$poin);
            MotivasiBelajar::milikPelajar($p->id_akun)->tambahPoin(1, $poin);
            $id[] = $p->id_akun;
        }

        $this->assertSame([40, 20, 10], MotivasiBelajar::ambilPoinKelas($id)->pluck('poin')->all());
    }

    #[TestDox('C22-UT17 Batas: poin sama urut id, tanpa data dianggap 0')]
    public function test_ut17_poin_kelas_seri(): void
    {
        $a = $this->buatPelajar('A');
        $b = $this->buatPelajar('B');
        $c = $this->buatPelajar('C');
        MotivasiBelajar::milikPelajar($b->id_akun)->tambahPoin(1, 5);
        MotivasiBelajar::milikPelajar($a->id_akun)->tambahPoin(1, 5);
        $hasil = MotivasiBelajar::ambilPoinKelas([$c->id_akun, $b->id_akun, $a->id_akun]);

        $this->assertSame([$a->id_akun, $b->id_akun, $c->id_akun], $hasil->pluck('id_pelajar')->all());
        $this->assertSame(0, $hasil->last()['poin']);
    }

    #[TestDox('C22-UT18 Negatif: tanpa anggota menghasilkan koleksi kosong')]
    public function test_ut18_poin_kelas_kosong(): void
    {
        $this->assertCount(0, MotivasiBelajar::ambilPoinKelas([]));
    }
}
