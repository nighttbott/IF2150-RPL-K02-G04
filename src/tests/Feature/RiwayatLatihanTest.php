<?php

namespace Tests\Feature;

use App\Models\RiwayatLatihan;
use App\Support\Periode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/** Pengujian C03 RiwayatLatihan (Q-008 sampai Q-016). */
class RiwayatLatihanTest extends TestCase
{
    use BantuanData, RefreshDatabase;

    public function test_simpan_hasil_menyimpan_data_latihan(): void
    {
        $pelajar = $this->buatPelajar();
        $materi = $this->buatMateri();

        $hasil = RiwayatLatihan::simpanHasil(['id_pelajar' => $pelajar->id_akun, 'id_materi' => $materi->id_materi,
            'target_soal' => 'ꦲ', 'mode' => 'menulis', 'skor' => 82.5, 'status' => 'berhasil']);

        $this->assertTrue($hasil->berhasil);
        $baris = RiwayatLatihan::find($hasil->ambil('idRiwayat'));
        $this->assertSame('latihan', $baris->mode_aktivitas);
        $this->assertSame('menulis', $baris->mode);
        $this->assertNotNull($baris->waktu_selesai);
    }

    public function test_simpan_hasil_menolak_data_tidak_valid(): void
    {
        $pelajar = $this->buatPelajar();
        $materi = $this->buatMateri();
        $dasar = ['id_pelajar' => $pelajar->id_akun, 'id_materi' => $materi->id_materi, 'target_soal' => 'ꦲ', 'mode' => 'menulis', 'skor' => 80, 'status' => 'berhasil'];

        $this->assertSame('SKOR_TIDAK_VALID', RiwayatLatihan::simpanHasil(['skor' => 120] + $dasar)->kode);
        $this->assertSame('MODE_TIDAK_VALID', RiwayatLatihan::simpanHasil(['mode' => 'menari'] + $dasar)->kode);
        $this->assertSame('STATUS_TIDAK_VALID', RiwayatLatihan::simpanHasil(['status' => 'selesai'] + $dasar)->kode);
        $this->assertSame('DATA_TIDAK_LENGKAP', RiwayatLatihan::simpanHasil(['id_pelajar' => 1])->kode);
        $this->assertSame(0, RiwayatLatihan::count());
    }

    public function test_mode_huruf_besar_disimpan_sebagai_huruf_kecil(): void
    {
        $pelajar = $this->buatPelajar();
        $materi = $this->buatMateri();

        $hasil = RiwayatLatihan::simpanHasil(['id_pelajar' => $pelajar->id_akun, 'id_materi' => $materi->id_materi,
            'target_soal' => 'ꦲ', 'mode' => 'MENULIS', 'skor' => 90, 'status' => 'berhasil']);

        $this->assertSame('menulis', RiwayatLatihan::find($hasil->ambil('idRiwayat'))->mode);
    }

    public function test_cek_pernah_selesai_hanya_menghitung_yang_berhasil(): void
    {
        $pelajar = $this->buatPelajar();
        $materi = $this->buatMateri();
        $this->riwayat($pelajar, $materi, 40, 'perlu_diulang');

        $this->assertFalse(RiwayatLatihan::cekPernahSelesai($pelajar->id_akun, 'ꦲ', 'menulis'));

        $this->riwayat($pelajar, $materi, 90, 'berhasil');
        $this->assertTrue(RiwayatLatihan::cekPernahSelesai($pelajar->id_akun, 'ꦲ', 'menulis'));
        $this->assertFalse(RiwayatLatihan::cekPernahSelesai($pelajar->id_akun, 'ꦲ', 'bunyi'));
    }

    public function test_penguasaan_adalah_rata_rata_skor_per_materi(): void
    {
        $pelajar = $this->buatPelajar();
        $m1 = $this->buatMateri('M1');
        $m2 = $this->buatMateri('M2');
        $this->riwayat($pelajar, $m1, 80);
        $this->riwayat($pelajar, $m1, 60, 'perlu_diulang');
        $this->riwayat($pelajar, $m2, 100);

        $this->assertEquals([$m1->id_materi => 70.0, $m2->id_materi => 100.0], RiwayatLatihan::hitungPenguasaan($pelajar->id_akun));
    }

    public function test_status_progres_sesuai_kontrak_modul_materi(): void
    {
        $pelajar = $this->buatPelajar();
        $belum = $this->buatMateri('Belum');
        $sedang = $this->buatMateri('Sedang');
        $selesai = $this->buatMateri('Selesai');
        $this->riwayat($pelajar, $sedang, 30, 'perlu_diulang');
        $this->riwayat($pelajar, $selesai, 90, 'berhasil');

        $status = RiwayatLatihan::ambilStatusProgres($pelajar->id_akun, [$belum->id_materi, $sedang->id_materi, $selesai->id_materi]);

        $this->assertSame(['status' => 'belum', 'persen' => 0], $status[$belum->id_materi]);
        $this->assertSame(['status' => 'sedang', 'persen' => 30], $status[$sedang->id_materi]);
        $this->assertSame(['status' => 'selesai', 'persen' => 90], $status[$selesai->id_materi]);
        // tanpa daftar: hanya materi yang punya riwayat
        $this->assertCount(2, RiwayatLatihan::ambilStatusProgres($pelajar->id_akun));
    }

    public function test_riwayat_disaring_sesuai_periode(): void
    {
        $pelajar = $this->buatPelajar();
        $materi = $this->buatMateri();
        $this->riwayat($pelajar, $materi, 80, 'berhasil', now()->subDays(2)->toDateTimeString());
        $this->riwayat($pelajar, $materi, 80, 'berhasil', now()->subDays(40)->toDateTimeString());

        $this->assertCount(1, RiwayatLatihan::ambilRiwayat($pelajar->id_akun, Periode::hariTerakhir(30)));
        $this->assertCount(2, RiwayatLatihan::ambilRiwayat($pelajar->id_akun, Periode::semua()));
    }

    public function test_pola_kesalahan_mengurutkan_aksara_paling_sering_salah(): void
    {
        $pelajar = $this->buatPelajar();
        $materi = $this->buatMateri();
        $this->riwayat($pelajar, $materi, 30, 'perlu_diulang', null, 'ꦤ');
        $this->riwayat($pelajar, $materi, 30, 'perlu_diulang', null, 'ꦤ');
        $this->riwayat($pelajar, $materi, 30, 'perlu_diulang', null, 'ꦕ');
        $this->riwayat($pelajar, $materi, 90, 'berhasil', null, 'ꦫ');

        $this->assertSame(['ꦤ' => 2, 'ꦕ' => 1], RiwayatLatihan::hitungPolaKesalahan($pelajar->id_akun));
    }

    public function test_catat_aktivitas_pengumpulan_tanpa_materi_diizinkan(): void
    {
        $pelajar = $this->buatPelajar();

        $hasil = RiwayatLatihan::catatAktivitas(['id_pelajar' => $pelajar->id_akun, 'mode_aktivitas' => 'pengumpulan']);

        $this->assertTrue($hasil->berhasil);
        $this->assertNull(RiwayatLatihan::find($hasil->ambil('idRiwayat'))->id_materi);
        $this->assertSame('AKTIVITAS_TIDAK_VALID', RiwayatLatihan::catatAktivitas(['id_pelajar' => $pelajar->id_akun, 'mode_aktivitas' => 'latihan'])->kode);
    }

    public function test_bukti_penyelesaian_mengabaikan_percobaan_yang_gagal(): void
    {
        $pelajar = $this->buatPelajar();
        $materi = $this->buatMateri();
        $idKelas = $this->buatKelas($this->buatPengajar());
        $idTugas = DB::table('tugas_kelas')->insertGetId(['id_kelas' => $idKelas, 'instruksi' => 'Uji', 'tenggat' => now()->addDay(), 'status' => 'terbit'], 'id_tugas');
        $k1 = DB::table('komponen_tugas')->insertGetId(['id_tugas' => $idTugas, 'jenis' => 'latihan', 'id_materi' => $materi->id_materi, 'mode' => 'menulis', 'wajib' => true], 'id_komponen');
        $k2 = DB::table('komponen_tugas')->insertGetId(['id_tugas' => $idTugas, 'jenis' => 'materi', 'id_materi' => $materi->id_materi, 'mode' => null, 'wajib' => true], 'id_komponen');

        RiwayatLatihan::simpanHasil(['id_pelajar' => $pelajar->id_akun, 'id_materi' => $materi->id_materi, 'target_soal' => 'ꦲ', 'mode' => 'menulis',
            'skor' => 40, 'status' => 'perlu_diulang', 'id_tugas' => $idTugas, 'id_komponen' => $k1]);
        RiwayatLatihan::catatAktivitas(['id_pelajar' => $pelajar->id_akun, 'id_materi' => $materi->id_materi, 'mode_aktivitas' => 'materi',
            'id_tugas' => $idTugas, 'id_komponen' => $k2]);

        $bukti = RiwayatLatihan::ambilBuktiPenyelesaian($pelajar->id_akun, $idTugas);

        $this->assertArrayHasKey($k2, $bukti);
        $this->assertArrayNotHasKey($k1, $bukti);
    }
}
