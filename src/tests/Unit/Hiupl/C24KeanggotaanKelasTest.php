<?php

namespace Tests\Unit\Hiupl;

use App\Models\KeanggotaanKelas;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\Feature\BantuanData;
use Tests\TestCase;

/**
 * HIUPL 4.2: pengujian unit kelas C24 KeanggotaanKelas (tingkat Mendalam: transaksi bergabung, status anggota, kontrol akses).
 */
class C24KeanggotaanKelasTest extends TestCase
{
    use BantuanData, RefreshDatabase;

    /** @uji C24-UT01 | Normal | simpanKeanggotaan(idPelajar: Integer, idKelas: Integer): HasilOperasi | Pelajar baru bergabung ke kelas aktif | berhasilBergabung; satu baris keanggotaan aktif */
    #[TestDox('C24-UT01 Normal: Pelajar bergabung ke kelas aktif')]
    public function test_ut01_bergabung(): void
    {
        $p = $this->buatPelajar();
        $k = $this->buatKelas($this->buatPengajar());

        $this->assertSame('berhasilBergabung', (new KeanggotaanKelas)->simpanKeanggotaan($p->id_akun, $k)->kode);
        $this->assertDatabaseHas('keanggotaan_kelas', ['id_kelas' => $k, 'id_pelajar' => $p->id_akun, 'status' => 'aktif']);
    }

    /** @uji C24-UT02 | Batas | simpanKeanggotaan(idPelajar: Integer, idKelas: Integer): HasilOperasi | (a) Pelajar yang sudah anggota bergabung lagi; (b) anggota berstatus keluar bergabung lagi | (a) sudahAnggota tanpa baris ganda; (b) anggotaDiaktifkan */
    #[TestDox('C24-UT02 Batas: bergabung ulang tidak menggandakan anggota')]
    public function test_ut02_bergabung_ulang(): void
    {
        $guru = $this->buatPengajar();
        $a = $this->buatPelajar('A');
        $b = $this->buatPelajar('B');
        $k = $this->buatKelas($guru);
        $this->gabung($k, $a);
        $this->gabung($k, $b, 'keluar');

        $this->assertSame('sudahAnggota', (new KeanggotaanKelas)->simpanKeanggotaan($a->id_akun, $k)->kode);
        $this->assertSame('anggotaDiaktifkan', (new KeanggotaanKelas)->simpanKeanggotaan($b->id_akun, $k)->kode);
        $this->assertSame(2, KeanggotaanKelas::count());
    }

    /** @uji C24-UT03 | Negatif | simpanKeanggotaan(idPelajar: Integer, idKelas: Integer): HasilOperasi | (a) kelas diarsipkan; (b) kelas tidak ada; (c) akun Pengajar mencoba bergabung | kelasTidakAktif, kelasTidakAktif, pelajarTidakValid; tidak ada baris */
    #[TestDox('C24-UT03 Negatif: kelas tidak aktif atau akun bukan Pelajar ditolak')]
    public function test_ut03_bergabung_ditolak(): void
    {
        $guru = $this->buatPengajar();
        $k = $this->buatKelas($guru);
        $arsip = $this->buatKelas($guru, false, 'Arsip');
        DB::table('kelas_belajar')->where('id_kelas', $arsip)->update(['status' => 'arsip']);
        $p = $this->buatPelajar();

        $this->assertSame('kelasTidakAktif', (new KeanggotaanKelas)->simpanKeanggotaan($p->id_akun, $arsip)->kode);
        $this->assertSame('kelasTidakAktif', (new KeanggotaanKelas)->simpanKeanggotaan($p->id_akun, 999999)->kode);
        $this->assertSame('pelajarTidakValid', (new KeanggotaanKelas)->simpanKeanggotaan($guru->id_akun, $k)->kode);
        $this->assertSame(0, KeanggotaanKelas::count());
    }

    /** @uji C24-UT04 | Normal | ubahStatus(status: String): HasilOperasi | Anggota aktif dikeluarkan (status keluar) | statusDiubah; periksaAkses menjadi false */
    #[TestDox('C24-UT04 Normal: anggota dapat dikeluarkan')]
    public function test_ut04_keluarkan(): void
    {
        $p = $this->buatPelajar();
        $k = $this->buatKelas($this->buatPengajar());
        $this->gabung($k, $p);
        $a = KeanggotaanKelas::cari($k, $p->id_akun);

        $this->assertSame('statusDiubah', $a->ubahStatus('keluar')->kode);
        $this->assertFalse(KeanggotaanKelas::periksaAkses($p->id_akun, $k));
    }

    /** @uji C24-UT05 | Batas | ubahStatus(status: String): HasilOperasi | Anggota keluar diaktifkan kembali pada kelas yang sudah diarsipkan; anggota dinonaktifkan pada kelas arsip | Aktivasi: kelasTidakAktif; nonaktif: statusDiubah */
    #[TestDox('C24-UT05 Batas: aktivasi pada kelas arsip ditolak, nonaktif tetap boleh')]
    public function test_ut05_kelas_arsip(): void
    {
        $p = $this->buatPelajar();
        $k = $this->buatKelas($this->buatPengajar());
        $this->gabung($k, $p, 'keluar');
        DB::table('kelas_belajar')->where('id_kelas', $k)->update(['status' => 'arsip']);
        $a = KeanggotaanKelas::cari($k, $p->id_akun);

        $this->assertSame('kelasTidakAktif', $a->ubahStatus('aktif')->kode);
        $this->assertSame('statusDiubah', $a->ubahStatus('nonaktif')->kode);
    }

    /** @uji C24-UT06 | Negatif | ubahStatus(status: String): HasilOperasi | Status "dihapus" (tidak dikenal) dan objek keanggotaan yang belum tersimpan | statusTidakValid */
    #[TestDox('C24-UT06 Negatif: status tidak dikenal ditolak')]
    public function test_ut06_status_tidak_valid(): void
    {
        $p = $this->buatPelajar();
        $k = $this->buatKelas($this->buatPengajar());
        $this->gabung($k, $p);

        $this->assertSame('statusTidakValid', KeanggotaanKelas::cari($k, $p->id_akun)->ubahStatus('dihapus')->kode);
        $this->assertSame('statusTidakValid', (new KeanggotaanKelas)->ubahStatus('keluar')->kode);
    }

    /** @uji C24-UT07 | Normal | periksaAkses(idPelajar: Integer, idKelas: Integer): Boolean | Anggota aktif | true */
    #[TestDox('C24-UT07 Normal: anggota aktif punya akses')]
    public function test_ut07_akses(): void
    {
        $p = $this->buatPelajar();
        $k = $this->buatKelas($this->buatPengajar());
        $this->gabung($k, $p);

        $this->assertTrue(KeanggotaanKelas::periksaAkses($p->id_akun, $k));
    }

    /** @uji C24-UT08 | Batas | periksaAkses(idPelajar: Integer, idKelas: Integer): Boolean | Anggota berstatus nonaktif | false */
    #[TestDox('C24-UT08 Batas: anggota nonaktif tidak punya akses')]
    public function test_ut08_akses_nonaktif(): void
    {
        $p = $this->buatPelajar();
        $k = $this->buatKelas($this->buatPengajar());
        $this->gabung($k, $p, 'nonaktif');

        $this->assertFalse(KeanggotaanKelas::periksaAkses($p->id_akun, $k));
    }

    /** @uji C24-UT09 | Negatif | periksaAkses(idPelajar: Integer, idKelas: Integer): Boolean | Pelajar yang bukan anggota | false */
    #[TestDox('C24-UT09 Negatif: bukan anggota tidak punya akses')]
    public function test_ut09_akses_bukan_anggota(): void
    {
        $this->assertFalse(KeanggotaanKelas::periksaAkses($this->buatPelajar()->id_akun, $this->buatKelas($this->buatPengajar())));
    }

    /** @uji C24-UT10 | Normal | daftarAnggotaAktif(idKelas: Integer): List | Dua anggota aktif dan satu anggota keluar | Dua anggota aktif */
    #[TestDox('C24-UT10 Normal: daftar anggota aktif saja')]
    public function test_ut10_daftar_aktif(): void
    {
        $k = $this->buatKelas($this->buatPengajar());
        $this->gabung($k, $this->buatPelajar('A'));
        $this->gabung($k, $this->buatPelajar('B'));
        $this->gabung($k, $this->buatPelajar('C'), 'keluar');

        $this->assertCount(2, KeanggotaanKelas::daftarAnggotaAktif($k));
        $this->assertCount(3, KeanggotaanKelas::daftarAnggota($k));
    }

    /** @uji C24-UT11 | Batas | daftarAnggotaAktif(idKelas: Integer): List | Kelas tanpa anggota | Daftar kosong */
    #[TestDox('C24-UT11 Batas: kelas kosong menghasilkan daftar kosong')]
    public function test_ut11_daftar_kosong(): void
    {
        $this->assertCount(0, KeanggotaanKelas::daftarAnggotaAktif($this->buatKelas($this->buatPengajar())));
    }

    /** @uji C24-UT12 | Negatif | daftarAnggotaAktif(idKelas: Integer): List | Anggota kelas lain | Tidak ikut terdaftar */
    #[TestDox('C24-UT12 Negatif: anggota kelas lain tidak ikut')]
    public function test_ut12_daftar_kelas_lain(): void
    {
        $guru = $this->buatPengajar();
        $k1 = $this->buatKelas($guru);
        $k2 = $this->buatKelas($guru, false, 'Lain');
        $this->gabung($k2, $this->buatPelajar());

        $this->assertCount(0, KeanggotaanKelas::daftarAnggotaAktif($k1));
    }

    /** @uji C24-UT13 | Normal | daftarAnggota(idKelas: Integer): List | Anggota aktif, keluar, dan nonaktif | Ketiganya terdaftar dengan statusnya (riwayat keanggotaan tidak hilang) */
    #[TestDox('C24-UT13 Normal: daftarAnggota memuat semua status')]
    public function test_ut13_daftar_semua(): void
    {
        $k = $this->buatKelas($this->buatPengajar());
        foreach (['aktif', 'keluar', 'nonaktif'] as $s) {
            $this->gabung($k, $this->buatPelajar($s), $s);
        }

        $this->assertSame(['aktif', 'keluar', 'nonaktif'], KeanggotaanKelas::daftarAnggota($k)->pluck('status')->all());
    }

    /** @uji C24-UT14 | Batas | daftarAnggota(idKelas: Integer): List | Anggota keluar lalu bergabung lagi | Tetap satu baris (tidak ganda) berstatus aktif */
    #[TestDox('C24-UT14 Batas: bergabung lagi tidak menambah baris')]
    public function test_ut14_gabung_lagi(): void
    {
        $k = $this->buatKelas($this->buatPengajar());
        $p = $this->buatPelajar();
        $this->gabung($k, $p, 'keluar');
        (new KeanggotaanKelas)->simpanKeanggotaan($p->id_akun, $k);

        $this->assertSame(['aktif'], KeanggotaanKelas::daftarAnggota($k)->pluck('status')->all());
    }

    /** @uji C24-UT15 | Negatif | daftarAnggota(idKelas: Integer): List | ID kelas yang tidak ada | Koleksi kosong */
    #[TestDox('C24-UT15 Negatif: kelas tidak ada')]
    public function test_ut15_daftar_kelas_tidak_ada(): void
    {
        $this->assertCount(0, KeanggotaanKelas::daftarAnggota(999999));
    }
}
