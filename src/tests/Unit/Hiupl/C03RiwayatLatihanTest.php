<?php

namespace Tests\Unit\Hiupl;

use App\Models\RiwayatLatihan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\Feature\BantuanData;
use Tests\TestCase;

class C03RiwayatLatihanTest extends TestCase
{
    use BantuanData, RefreshDatabase;

    private function tugas(): array
    {
        $k = $this->buatKelas($this->buatPengajar());
        $t = \Illuminate\Support\Facades\DB::table('tugas_kelas')->insertGetId(['id_kelas' => $k, 'instruksi' => 'X', 'tenggat' => now()->addDay(), 'status' => 'terbit'], 'id_tugas');
        $c = \Illuminate\Support\Facades\DB::table('komponen_tugas')->insertGetId(['id_tugas' => $t, 'jenis' => 'materi', 'id_materi' => $this->buatMateri('Tugas')->id_materi, 'mode' => null, 'wajib' => true], 'id_komponen');

        return [$t, $c];
    }

    private function hasil(array $ganti = []): array
    {
        return $ganti + ['id_pelajar' => $this->buatPelajar()->id_akun, 'id_materi' => $this->buatMateri()->id_materi, 'target_soal' => 'ꦲ', 'mode' => 'bunyi', 'skor' => 80, 'status' => 'berhasil'];
    }

    #[TestDox('C03-UT01 Normal: hasil latihan valid tersimpan')]
    public function test_ut01_simpan_hasil(): void
    {
        $h = RiwayatLatihan::simpanHasil($this->hasil());
        $r = RiwayatLatihan::find($h->ambil('idRiwayat'));

        $this->assertTrue($h->berhasil);
        $this->assertSame('latihan', $r->mode_aktivitas->value ?? $r->mode_aktivitas);
        $this->assertNotNull($r->waktu_selesai);
    }

    #[TestDox('C03-UT02 Batas: skor 0 dan 100 diterima')]
    public function test_ut02_skor_batas(): void
    {
        $nol = RiwayatLatihan::simpanHasil($this->hasil(['skor' => 0, 'status' => 'perlu_diulang', 'mode' => 'BUNYI']));
        $penuh = RiwayatLatihan::simpanHasil($this->hasil(['skor' => 100]));

        $this->assertTrue($nol->berhasil && $penuh->berhasil);
        $this->assertNull(RiwayatLatihan::find($nol->ambil('idRiwayat'))->waktu_selesai);
    }

    public function test_ut03_hasil_tidak_valid(): void
    {
        $dasar = $this->hasil();
        $this->assertSame('SKOR_TIDAK_VALID', RiwayatLatihan::simpanHasil(['skor' => 100.01] + $dasar)->kode);
        $this->assertSame('SKOR_TIDAK_VALID', RiwayatLatihan::simpanHasil(['skor' => -1] + $dasar)->kode);
        $this->assertSame('STATUS_TIDAK_VALID', RiwayatLatihan::simpanHasil(['status' => 'selesai'] + $dasar)->kode);
        $this->assertSame('MODE_TIDAK_VALID', RiwayatLatihan::simpanHasil(['mode' => 'menggambar'] + $dasar)->kode);
        unset($dasar['skor']);
        $this->assertSame('DATA_TIDAK_LENGKAP', RiwayatLatihan::simpanHasil($dasar)->kode);
        $this->assertSame(0, RiwayatLatihan::count());
    }

    #[TestDox('C03-UT04 Normal: aktivitas materi tercatat')]
    public function test_ut04_catat_aktivitas(): void
    {
        $h = RiwayatLatihan::catatAktivitas(['id_pelajar' => $this->buatPelajar()->id_akun, 'mode_aktivitas' => 'materi', 'id_materi' => $this->buatMateri()->id_materi]);

        $this->assertTrue($h->berhasil);
        $this->assertDatabaseHas('riwayat_latihan', ['id_riwayat' => $h->ambil('idRiwayat'), 'status' => 'selesai', 'mode_aktivitas' => 'materi']);
    }

    #[TestDox('C03-UT05 Batas: aktivitas tanpa materi dan huruf besar diterima')]
    public function test_ut05_catat_tanpa_materi(): void
    {
        $h = RiwayatLatihan::catatAktivitas(['id_pelajar' => $this->buatPelajar()->id_akun, 'mode_aktivitas' => 'PENGUMPULAN']);

        $this->assertTrue($h->berhasil);
        $this->assertNull(RiwayatLatihan::find($h->ambil('idRiwayat'))->id_materi);
    }

    #[TestDox('C03-UT06 Negatif: aktivitas latihan atau data tidak lengkap ditolak')]
    public function test_ut06_catat_ditolak(): void
    {
        $this->assertSame('AKTIVITAS_TIDAK_VALID', RiwayatLatihan::catatAktivitas(['id_pelajar' => 1, 'mode_aktivitas' => 'latihan'])->kode);
        $this->assertSame('DATA_TIDAK_LENGKAP', RiwayatLatihan::catatAktivitas(['mode_aktivitas' => 'materi'])->kode);
    }

    #[TestDox('C03-UT07 Normal: soal yang pernah berhasil terdeteksi')]
    public function test_ut07_pernah_selesai(): void
    {
        $p = $this->buatPelajar();
        $this->riwayat($p, $this->buatMateri(), 90);

        $this->assertTrue(RiwayatLatihan::cekPernahSelesai($p->id_akun, 'ꦲ', 'menulis'));
    }

    #[TestDox('C03-UT08 Batas: mode dibedakan dan tidak peka huruf besar')]
    public function test_ut08_pernah_selesai_mode(): void
    {
        $p = $this->buatPelajar();
        $this->riwayat($p, $this->buatMateri(), 90);

        $this->assertFalse(RiwayatLatihan::cekPernahSelesai($p->id_akun, 'ꦲ', 'bunyi'));
        $this->assertTrue(RiwayatLatihan::cekPernahSelesai($p->id_akun, 'ꦲ', 'MENULIS'));
    }

    #[TestDox('C03-UT09 Negatif: riwayat perlu diulang tidak dihitung selesai')]
    public function test_ut09_belum_selesai(): void
    {
        $p = $this->buatPelajar();
        $this->riwayat($p, $this->buatMateri(), 40, 'perlu_diulang');

        $this->assertFalse(RiwayatLatihan::cekPernahSelesai($p->id_akun, 'ꦲ', 'menulis'));
    }

    #[TestDox('C03-UT10 Normal: status progres per materi')]
    public function test_ut10_status_progres(): void
    {
        $p = $this->buatPelajar();
        $a = $this->buatMateri('A');
        $b = $this->buatMateri('B');
        $this->riwayat($p, $a, 90);
        $this->riwayat($p, $b, 40, 'perlu_diulang');
        $this->riwayat($p, $b, 60);
        $s = RiwayatLatihan::ambilStatusProgres($p->id_akun, [$a->id_materi, $b->id_materi]);

        $this->assertSame(['status' => 'selesai', 'persen' => 90], $s[$a->id_materi]);
        $this->assertSame(['status' => 'sedang', 'persen' => 50], $s[$b->id_materi]);
    }

    #[TestDox('C03-UT11 Batas: rata-rata tepat ambang 70 dianggap selesai')]
    public function test_ut11_ambang_tuntas(): void
    {
        $p = $this->buatPelajar();
        $a = $this->buatMateri('A');
        $b = $this->buatMateri('B');
        $this->riwayat($p, $a, 70);
        $this->riwayat($p, $b, 69.99, 'perlu_diulang');
        $s = RiwayatLatihan::ambilStatusProgres($p->id_akun, [$a->id_materi, $b->id_materi]);

        $this->assertSame('selesai', $s[$a->id_materi]['status']);
        $this->assertSame('sedang', $s[$b->id_materi]['status']);
    }

    #[TestDox('C03-UT12 Negatif: materi tanpa riwayat berstatus belum')]
    public function test_ut12_tanpa_riwayat(): void
    {
        $m = $this->buatMateri();

        $this->assertSame(['status' => 'belum', 'persen' => 0], RiwayatLatihan::ambilStatusProgres($this->buatPelajar()->id_akun, [$m->id_materi])[$m->id_materi]);
    }

    #[TestDox('C03-UT13 Normal: penguasaan adalah rata-rata skor')]
    public function test_ut13_penguasaan(): void
    {
        $p = $this->buatPelajar();
        $m = $this->buatMateri();
        $this->riwayat($p, $m, 80);
        $this->riwayat($p, $m, 60, 'perlu_diulang');

        $this->assertSame([$m->id_materi => 70.0], RiwayatLatihan::hitungPenguasaan($p->id_akun));
    }

    #[TestDox('C03-UT14 Batas: riwayat Pelajar lain dan aktivitas tanpa skor diabaikan')]
    public function test_ut14_penguasaan_terpisah(): void
    {
        $p = $this->buatPelajar();
        $this->riwayat($this->buatPelajar('Lain'), $this->buatMateri(), 90);
        RiwayatLatihan::catatAktivitas(['id_pelajar' => $p->id_akun, 'mode_aktivitas' => 'materi', 'id_materi' => $this->buatMateri('M2')->id_materi]);

        $this->assertSame([], RiwayatLatihan::hitungPenguasaan($p->id_akun));
    }

    #[TestDox('C03-UT15 Negatif: Pelajar tidak dikenal menghasilkan map kosong')]
    public function test_ut15_penguasaan_tidak_ada(): void
    {
        $this->assertSame([], RiwayatLatihan::hitungPenguasaan(999999));
    }

    #[TestDox('C03-UT16 Normal: pola kesalahan diurutkan dari yang terbanyak')]
    public function test_ut16_pola_kesalahan(): void
    {
        $p = $this->buatPelajar();
        $m = $this->buatMateri();
        foreach (['ꦤ', 'ꦤ', 'ꦤ', 'ꦲ'] as $t) {
            $this->riwayat($p, $m, 30, 'perlu_diulang', null, $t);
        }

        $this->assertSame(['ꦤ' => 3, 'ꦲ' => 1], RiwayatLatihan::hitungPolaKesalahan($p->id_akun));
    }

    #[TestDox('C03-UT17 Batas: pola kesalahan dibatasi lima aksara')]
    public function test_ut17_pola_maks_lima(): void
    {
        $p = $this->buatPelajar();
        $m = $this->buatMateri();
        foreach (['ꦲ', 'ꦤ', 'ꦕ', 'ꦫ', 'ꦏ', 'ꦢ'] as $t) {
            $this->riwayat($p, $m, 30, 'perlu_diulang', null, $t);
        }

        $this->assertCount(5, RiwayatLatihan::hitungPolaKesalahan($p->id_akun));
    }

    #[TestDox('C03-UT18 Negatif: tanpa kesalahan menghasilkan map kosong')]
    public function test_ut18_tanpa_kesalahan(): void
    {
        $p = $this->buatPelajar();
        $this->riwayat($p, $this->buatMateri(), 95);

        $this->assertSame([], RiwayatLatihan::hitungPolaKesalahan($p->id_akun));
    }

    #[TestDox('C03-UT19 Normal: riwayat urut terbaru')]
    public function test_ut19_ambil_riwayat(): void
    {
        $p = $this->buatPelajar();
        $m = $this->buatMateri();
        foreach (['2026-11-01', '2026-11-03', '2026-11-02'] as $t) {
            $this->riwayat($p, $m, 80, 'berhasil', $t.' 10:00:00');
        }
        $r = RiwayatLatihan::ambilRiwayat($p->id_akun, \App\Support\Periode::semua());

        $this->assertSame(['2026-11-03', '2026-11-02', '2026-11-01'], $r->map(fn ($x) => $x->tanggal_pengerjaan->toDateString())->all());
        $this->assertSame('Nglegena 1', $r->first()->materi->nama_materi);
    }

    #[TestDox('C03-UT20 Batas: batas awal periode')]
    public function test_ut20_batas_periode(): void
    {
        $p = $this->buatPelajar();
        $m = $this->buatMateri();
        $this->riwayat($p, $m, 80, 'berhasil', now()->subDays(6)->startOfDay()->toDateTimeString());
        $this->riwayat($p, $m, 80, 'berhasil', now()->subDays(7)->endOfDay()->toDateTimeString());

        $this->assertCount(1, RiwayatLatihan::ambilRiwayat($p->id_akun, \App\Support\Periode::hariTerakhir(7)));
    }

    #[TestDox('C03-UT21 Negatif: riwayat Pelajar lain tidak terbawa')]
    public function test_ut21_riwayat_lain(): void
    {
        $this->riwayat($this->buatPelajar('Lain'), $this->buatMateri(), 80);

        $this->assertCount(0, RiwayatLatihan::ambilRiwayat($this->buatPelajar()->id_akun, \App\Support\Periode::semua()));
    }

    #[TestDox('C03-UT22 Normal: bukti penyelesaian per komponen')]
    public function test_ut22_bukti(): void
    {
        $p = $this->buatPelajar();
        [$t, $c] = $this->tugas();
        RiwayatLatihan::catatAktivitas(['id_pelajar' => $p->id_akun, 'mode_aktivitas' => 'tugas', 'id_tugas' => $t, 'id_komponen' => $c]);

        $this->assertSame([$c], array_keys(RiwayatLatihan::ambilBuktiPenyelesaian($p->id_akun, $t)));
    }

    #[TestDox('C03-UT23 Batas: komponen ganda menjadi satu bukti')]
    public function test_ut23_bukti_ganda(): void
    {
        $p = $this->buatPelajar();
        [$t, $c] = $this->tugas();
        RiwayatLatihan::catatAktivitas(['id_pelajar' => $p->id_akun, 'mode_aktivitas' => 'tugas', 'id_tugas' => $t, 'id_komponen' => $c, 'waktu_selesai' => '2026-11-01 08:00:00']);
        RiwayatLatihan::catatAktivitas(['id_pelajar' => $p->id_akun, 'mode_aktivitas' => 'tugas', 'id_tugas' => $t, 'id_komponen' => $c, 'waktu_selesai' => '2026-11-02 08:00:00']);
        $b = RiwayatLatihan::ambilBuktiPenyelesaian($p->id_akun, $t);

        $this->assertCount(1, $b);
        $this->assertStringStartsWith('2026-11-02', (string) $b[$c]);
    }

    #[TestDox('C03-UT24 Negatif: latihan gagal bukan bukti')]
    public function test_ut24_bukti_gagal(): void
    {
        $p = $this->buatPelajar();
        [$t, $c] = $this->tugas();
        RiwayatLatihan::simpanHasil($this->hasil(['id_pelajar' => $p->id_akun, 'status' => 'perlu_diulang', 'skor' => 20, 'id_tugas' => $t, 'id_komponen' => $c]));

        $this->assertSame([], RiwayatLatihan::ambilBuktiPenyelesaian($p->id_akun, $t));
    }

    #[TestDox('C03-UT25 Normal: riwayat per materi dan per Pelajar')]
    public function test_ut25_riwayat_materi_pelajar(): void
    {
        $p = $this->buatPelajar();
        $a = $this->buatMateri('A');
        $this->riwayat($p, $a, 80);
        $this->riwayat($p, $a, 60, 'perlu_diulang');
        $this->riwayat($p, $this->buatMateri('B'), 90);

        $this->assertCount(2, RiwayatLatihan::ambilRiwayatMateri($p->id_akun, $a->id_materi));
        $this->assertCount(3, RiwayatLatihan::ambilRiwayatPelajar($p->id_akun));
    }

    #[TestDox('C03-UT26 Batas: urutan stabil saat waktu sama')]
    public function test_ut26_urutan_stabil(): void
    {
        $p = $this->buatPelajar();
        $m = $this->buatMateri();
        $r1 = $this->riwayat($p, $m, 80, 'berhasil', '2026-11-01 10:00:00');
        $r2 = $this->riwayat($p, $m, 70, 'berhasil', '2026-11-01 10:00:00');

        $this->assertSame([$r2->id_riwayat, $r1->id_riwayat], RiwayatLatihan::ambilRiwayatPelajar($p->id_akun)->pluck('id_riwayat')->all());
    }

    #[TestDox('C03-UT27 Negatif: tanpa riwayat menghasilkan koleksi kosong')]
    public function test_ut27_riwayat_kosong(): void
    {
        $p = $this->buatPelajar();

        $this->assertCount(0, RiwayatLatihan::ambilRiwayatMateri($p->id_akun, $this->buatMateri()->id_materi));
        $this->assertCount(0, RiwayatLatihan::ambilRiwayatPelajar($p->id_akun));
    }
}
