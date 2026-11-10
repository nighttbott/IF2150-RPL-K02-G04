<?php

namespace Tests\Feature;

use App\Models\MotivasiBelajar;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Pengujian C22 MotivasiBelajar (Algo-028 sampai Algo-033).
 */
class MotivasiBelajarTest extends TestCase
{
    use BantuanData, RefreshDatabase;

    public function test_streak_bertambah_bila_berlatih_hari_berikutnya(): void
    {
        $m = MotivasiBelajar::milikPelajar($this->buatPelajar()->id_akun);

        $m->perbaruiStreak('2026-11-01');
        $m->perbaruiStreak('2026-11-02');
        $m->perbaruiStreak('2026-11-03');

        $this->assertSame(3, $m->fresh()->streak);
    }

    public function test_streak_tidak_berubah_bila_berlatih_di_hari_yang_sama(): void
    {
        $m = MotivasiBelajar::milikPelajar($this->buatPelajar()->id_akun);

        $m->perbaruiStreak('2026-11-01');
        $m->perbaruiStreak('2026-11-01');

        $this->assertSame(1, $m->fresh()->streak);
    }

    public function test_streak_kembali_ke_satu_bila_ada_hari_terlewat(): void
    {
        $m = MotivasiBelajar::milikPelajar($this->buatPelajar()->id_akun);

        $m->perbaruiStreak('2026-11-01');
        $m->perbaruiStreak('2026-11-02');
        $m->perbaruiStreak('2026-11-05');

        $this->assertSame(1, $m->fresh()->streak);
        $this->assertSame('2026-11-05', $m->fresh()->tanggal_aktif_terakhir->toDateString());
    }

    public function test_tambah_poin_menolak_nilai_nol_atau_negatif(): void
    {
        $m = MotivasiBelajar::milikPelajar($this->buatPelajar()->id_akun);

        $this->assertFalse($m->tambahPoin(1, 0)->berhasil);
        $this->assertFalse($m->tambahPoin(1, -5)->berhasil);
        $this->assertSame(0, $m->fresh()->poin);
    }

    public function test_tambah_poin_menjumlahkan_dan_mengembalikan_total(): void
    {
        $m = MotivasiBelajar::milikPelajar($this->buatPelajar()->id_akun);

        $m->tambahPoin(1, 10);
        $hasil = $m->tambahPoin(2, 10);

        $this->assertTrue($hasil->berhasil);
        $this->assertSame(20, $hasil->ambil('poin'));
        $this->assertSame(20, $m->fresh()->poin);
    }

    public function test_badge_yang_sama_tidak_disimpan_dua_kali(): void
    {
        $m = MotivasiBelajar::milikPelajar($this->buatPelajar()->id_akun);

        $this->assertTrue($m->berikanBadge('pemula')->ambil('baru'));
        $this->assertFalse($m->berikanBadge('pemula')->ambil('baru'));
        $this->assertSame(['pemula'], $m->fresh()->badge());
    }

    public function test_streak_terputus_ditampilkan_nol(): void
    {
        $m = MotivasiBelajar::milikPelajar($this->buatPelajar()->id_akun);
        $m->perbaruiStreak(CarbonImmutable::today()->subDays(3));

        $this->assertSame(0, MotivasiBelajar::ambilMotivasi($m->id_pelajar)['streak']);
        $this->assertSame(1, $m->fresh()->streak); // data tersimpan tidak diubah
    }

    public function test_motivasi_pelajar_tanpa_baris_dianggap_nol(): void
    {
        $data = MotivasiBelajar::ambilMotivasi(999999);

        $this->assertSame(0, $data['poin']);
        $this->assertSame([], $data['daftarBadge']);
    }

    public function test_scoreboard_poin_sama_mendapat_peringkat_sama(): void
    {
        $a = $this->buatPelajar('A');
        $b = $this->buatPelajar('B');
        $c = $this->buatPelajar('C');
        $d = $this->buatPelajar('D'); // belum punya baris motivasi -> poin 0
        MotivasiBelajar::milikPelajar($a->id_akun)->tambahPoin(1, 50);
        MotivasiBelajar::milikPelajar($b->id_akun)->tambahPoin(1, 30);
        MotivasiBelajar::milikPelajar($c->id_akun)->tambahPoin(1, 30);

        $papan = MotivasiBelajar::susunScoreboard([$a->id_akun => 'A', $b->id_akun => 'B', $c->id_akun => 'C', $d->id_akun => 'D']);

        $this->assertSame([1, 2, 2, 4], array_column($papan, 'peringkat'));
        $this->assertSame(['A', 'B', 'C', 'D'], array_column($papan, 'nama_tampilan'));
        $this->assertArrayNotHasKey('email', $papan[0]); // tidak membuka data pribadi
    }
}
