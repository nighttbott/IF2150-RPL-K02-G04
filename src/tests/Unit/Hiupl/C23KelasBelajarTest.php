<?php

namespace Tests\Unit\Hiupl;

use App\Models\KelasBelajar;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\Feature\BantuanData;
use Tests\TestCase;

/**
 * HIUPL 4.2: pengujian unit kelas C23 KelasBelajar (tingkat Mendalam: validasi nama, kode bergabung unik, kepemilikan).
 */
class C23KelasBelajarTest extends TestCase
{
    use BantuanData, RefreshDatabase;

    private function kelas(): array
    {
        $guru = $this->buatPengajar();
        $k = new KelasBelajar;
        $k->simpan(['nama' => 'Kelas 7A', 'pemilik_id' => $guru->id_akun]);

        return [$guru, $k];
    }

    /** @uji C23-UT01 | Normal | simpan(data: DataKelas): HasilOperasi | Nama "Kelas 7A" dan pemilik Pengajar | Berhasil; kelas aktif dengan kode 6 karakter dan scoreboard nonaktif */
    #[TestDox('C23-UT01 Normal: kelas baru tersimpan dengan kode bergabung')]
    public function test_ut01_simpan(): void
    {
        [, $k] = $this->kelas();

        $this->assertMatchesRegularExpression('/^[A-Z2-9]{6}$/', $k->kode_bergabung);
        $this->assertDatabaseHas('kelas_belajar', ['id_kelas' => $k->id_kelas, 'status' => 'aktif', 'status_scoreboard' => false]);
    }

    /** @uji C23-UT02 | Batas | simpan(data: DataKelas): HasilOperasi | Nama tepat 100 karakter, dan nama 101 karakter | 100 karakter diterima; 101 karakter NAMA_TIDAK_VALID */
    #[TestDox('C23-UT02 Batas: nama 100 karakter diterima, 101 ditolak')]
    public function test_ut02_nama_batas(): void
    {
        $id = $this->buatPengajar()->id_akun;

        $this->assertTrue((new KelasBelajar)->simpan(['nama' => str_repeat('a', 100), 'pemilik_id' => $id])->berhasil);
        $this->assertSame('NAMA_TIDAK_VALID', (new KelasBelajar)->simpan(['nama' => str_repeat('a', 101), 'pemilik_id' => $id])->kode);
    }

    /** @uji C23-UT03 | Negatif | simpan(data: DataKelas): HasilOperasi | Nama berisi spasi saja; pemilik kosong | NAMA_TIDAK_VALID dan PEMILIK_TIDAK_VALID; tidak ada kelas tersimpan */
    #[TestDox('C23-UT03 Negatif: nama kosong atau tanpa pemilik ditolak')]
    public function test_ut03_simpan_ditolak(): void
    {
        $this->assertSame('NAMA_TIDAK_VALID', (new KelasBelajar)->simpan(['nama' => '   ', 'pemilik_id' => 1])->kode);
        $this->assertSame('PEMILIK_TIDAK_VALID', (new KelasBelajar)->simpan(['nama' => 'Kelas'])->kode);
        $this->assertSame(0, KelasBelajar::count());
    }

    /** @uji C23-UT04 | Normal | buatKodeBergabung(): String | Dipanggil 50 kali | Setiap kode 6 karakter dari alfabet tanpa huruf/angka ambigu (I, O, 0, 1) */
    #[TestDox('C23-UT04 Normal: kode bergabung memakai alfabet tanpa karakter ambigu')]
    public function test_ut04_format_kode(): void
    {
        $k = new KelasBelajar;
        for ($i = 0; $i < 50; $i++) {
            $this->assertMatchesRegularExpression('/^[ABCDEFGHJKLMNPQRSTUVWXYZ2-9]{6}$/', $k->buatKodeBergabung());
        }
    }

    /** @uji C23-UT05 | Batas | buatKodeBergabung(): String | Kode yang sudah dipakai kelas lain ada di basis data | Kode baru tidak sama dengan kode yang sudah tersimpan */
    #[TestDox('C23-UT05 Batas: kode baru tidak bentrok dengan kode tersimpan')]
    public function test_ut05_kode_unik(): void
    {
        [, $k] = $this->kelas();

        $this->assertNotSame($k->kode_bergabung, (new KelasBelajar)->buatKodeBergabung());
    }

    /** @uji C23-UT06 | Normal | gantiKodeBergabung(idPengajar: Integer): HasilOperasi | Pemilik kelas mengganti kode | Berhasil; kode di basis data berubah dan kode lama tidak berlaku */
    #[TestDox('C23-UT06 Normal: pemilik dapat mengganti kode')]
    public function test_ut06_ganti_kode(): void
    {
        [$guru, $k] = $this->kelas();
        $lama = $k->kode_bergabung;
        $h = $k->gantiKodeBergabung($guru->id_akun);

        $this->assertTrue($h->berhasil);
        $this->assertNotSame($lama, $h->ambil('kodeBergabung'));
        $this->assertSame(0, KelasBelajar::where('kode_bergabung', $lama)->count());
    }

    /** @uji C23-UT07 | Batas | gantiKodeBergabung(idPengajar: Integer): HasilOperasi | Pemilik mengganti kode pada kelas yang sudah diarsipkan | TIDAK_DITEMUKAN */
    #[TestDox('C23-UT07 Batas: kelas tidak aktif tidak dapat diganti kodenya')]
    public function test_ut07_ganti_kode_kelas_arsip(): void
    {
        [$guru, $k] = $this->kelas();
        DB::table('kelas_belajar')->where('id_kelas', $k->id_kelas)->update(['status' => 'arsip']);

        $this->assertSame('TIDAK_DITEMUKAN', $k->gantiKodeBergabung($guru->id_akun)->kode);
    }

    /** @uji C23-UT08 | Negatif | gantiKodeBergabung(idPengajar: Integer): HasilOperasi | Pengajar lain (bukan pemilik) mengganti kode | AKSES_DITOLAK; kode tidak berubah */
    #[TestDox('C23-UT08 Negatif: bukan pemilik ditolak')]
    public function test_ut08_ganti_kode_bukan_pemilik(): void
    {
        [, $k] = $this->kelas();
        $lama = $k->kode_bergabung;

        $this->assertSame('AKSES_DITOLAK', $k->gantiKodeBergabung($this->buatPengajar('Pak Lain')->id_akun)->kode);
        $this->assertSame($lama, $k->fresh()->kode_bergabung);
    }

    /** @uji C23-UT09 | Normal | periksaPemilik(idPengajar: Integer): Boolean | ID pemilik kelas | true */
    #[TestDox('C23-UT09 Normal: pemilik dikenali')]
    public function test_ut09_pemilik(): void
    {
        [$guru, $k] = $this->kelas();

        $this->assertTrue($k->periksaPemilik($guru->id_akun));
    }

    /** @uji C23-UT10 | Batas | periksaPemilik(idPengajar: Integer): Boolean | null (sesi tanpa akun) | false */
    #[TestDox('C23-UT10 Batas: null bukan pemilik')]
    public function test_ut10_pemilik_null(): void
    {
        [, $k] = $this->kelas();

        $this->assertFalse($k->periksaPemilik(null));
    }

    /** @uji C23-UT11 | Negatif | periksaPemilik(idPengajar: Integer): Boolean | ID Pengajar lain | false */
    #[TestDox('C23-UT11 Negatif: Pengajar lain bukan pemilik')]
    public function test_ut11_bukan_pemilik(): void
    {
        [, $k] = $this->kelas();

        $this->assertFalse($k->periksaPemilik($this->buatPengajar('Pak Lain')->id_akun));
    }

    /** @uji C23-UT12 | Normal | aturScoreboard(aktif: Boolean): HasilOperasi | Aktifkan papan peringkat | Berhasil; statusScoreboard aktif dan kolom bernilai true */
    #[TestDox('C23-UT12 Normal: papan peringkat diaktifkan')]
    public function test_ut12_aktifkan_scoreboard(): void
    {
        [, $k] = $this->kelas();

        $this->assertSame('aktif', $k->aturScoreboard(true)->ambil('statusScoreboard'));
        $this->assertTrue((bool) $k->fresh()->status_scoreboard);
    }

    /** @uji C23-UT13 | Batas | aturScoreboard(aktif: Boolean): HasilOperasi | Aktifkan dua kali lalu nonaktifkan | Tetap berhasil (idempoten); status akhir nonaktif */
    #[TestDox('C23-UT13 Batas: pengaturan berulang tetap konsisten')]
    public function test_ut13_scoreboard_berulang(): void
    {
        [, $k] = $this->kelas();
        $k->aturScoreboard(true);
        $k->aturScoreboard(true);

        $this->assertSame('nonaktif', $k->aturScoreboard(false)->ambil('statusScoreboard'));
        $this->assertFalse((bool) $k->fresh()->status_scoreboard);
    }

    /** @uji C23-UT14 | Negatif | aturScoreboard(aktif: Boolean): HasilOperasi | Objek kelas yang tidak valid (tanpa nama dan pemilik, gagal disimpan) | GAGAL_SIMPAN */
    #[TestDox('C23-UT14 Negatif: kegagalan simpan dilaporkan')]
    public function test_ut14_scoreboard_gagal(): void
    {
        $this->assertSame('GAGAL_SIMPAN', (new KelasBelajar)->aturScoreboard(true)->kode);
    }

    /** @uji C23-UT15 | Normal | daftarKelasPengajar(idPengajar: Integer): List | Pengajar memiliki dua kelas aktif (B dan A) | Dua kelas urut nama (A, B) */
    #[TestDox('C23-UT15 Normal: daftar kelas Pengajar urut nama')]
    public function test_ut15_daftar_kelas(): void
    {
        $guru = $this->buatPengajar();
        $this->buatKelas($guru, false, 'B');
        $this->buatKelas($guru, false, 'A');

        $this->assertSame(['A', 'B'], KelasBelajar::daftarKelasPengajar($guru->id_akun)->pluck('nama')->all());
    }

    /** @uji C23-UT16 | Batas | daftarKelasPengajar(idPengajar: Integer): List | Satu kelas aktif dan satu kelas arsip | Hanya kelas aktif */
    #[TestDox('C23-UT16 Batas: kelas arsip tidak tampil')]
    public function test_ut16_daftar_tanpa_arsip(): void
    {
        $guru = $this->buatPengajar();
        $this->buatKelas($guru, false, 'Aktif');
        DB::table('kelas_belajar')->where('id_kelas', $this->buatKelas($guru, false, 'Lama'))->update(['status' => 'arsip']);

        $this->assertSame(['Aktif'], KelasBelajar::daftarKelasPengajar($guru->id_akun)->pluck('nama')->all());
    }

    /** @uji C23-UT17 | Negatif | daftarKelasPengajar(idPengajar: Integer): List | Pengajar lain tanpa kelas | Daftar kosong (kelas Pengajar lain tidak terlihat) */
    #[TestDox('C23-UT17 Negatif: kelas Pengajar lain tidak terlihat')]
    public function test_ut17_daftar_pengajar_lain(): void
    {
        $this->kelas();

        $this->assertCount(0, KelasBelajar::daftarKelasPengajar($this->buatPengajar('Pak Lain')->id_akun));
    }
}
