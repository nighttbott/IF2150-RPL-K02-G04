<?php

namespace Tests\Unit\Hiupl;

use App\Http\Controllers\OtentikasiController;
use App\Models\AkunPengguna;
use App\Models\MotivasiBelajar;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\Feature\BantuanData;
use Tests\TestCase;

/**
 * HIUPL 4.2: pengujian unit kelas C15 OtentikasiController (tingkat Mendalam: keamanan, validasi data, hash kata sandi).
 */
class C15OtentikasiControllerTest extends TestCase
{
    use BantuanData, RefreshDatabase;

    private function kontroler(): OtentikasiController
    {
        return app(OtentikasiController::class);
    }

    private function isian(array $ganti = []): array
    {
        return $ganti + ['peran' => 'pelajar', 'email' => 'dewi@contoh.test', 'username' => 'dewi_01', 'password' => 'Rahasia123', 'setuju_tnc' => '1'];
    }

    /** @uji C15-UT01 | Normal | verifikasiFormatData(data: DataPendaftaran) | Kirim formulir daftar yang valid ke POST /daftar | Tidak ada galat validasi; akun dibuat */
    #[TestDox('C15-UT01 Normal: data pendaftaran valid diterima')]
    public function test_ut01_format_valid(): void
    {
        $this->post(route('daftar.proses'), $this->isian())->assertSessionHasNoErrors();
        $this->assertDatabaseHas('akun_pengguna', ['email' => 'dewi@contoh.test']);
    }

    /** @uji C15-UT02 | Batas | verifikasiFormatData(data: DataPendaftaran) | Kata sandi tepat 8 karakter (huruf + angka) dan username tepat 3 karakter | Diterima tanpa galat */
    #[TestDox('C15-UT02 Batas: sandi 8 karakter dan username 3 karakter diterima')]
    public function test_ut02_format_batas_bawah(): void
    {
        $this->post(route('daftar.proses'), $this->isian(['username' => 'dwi', 'password' => 'abcdefg1']))->assertSessionHasNoErrors();
    }

    /** @uji C15-UT03 | Negatif | verifikasiFormatData(data: DataPendaftaran) | Sandi 7 karakter, sandi tanpa angka, email tidak valid, T&C tidak dicentang, peran tim_materi | Ditolak dengan galat pada isian terkait; tidak ada akun dibuat */
    #[TestDox('C15-UT03 Negatif: data pendaftaran tidak valid ditolak')]
    public function test_ut03_format_tidak_valid(): void
    {
        $this->post(route('daftar.proses'), $this->isian(['password' => 'abcdef1']))->assertSessionHasErrors('password');
        $this->post(route('daftar.proses'), $this->isian(['password' => 'abcdefgh']))->assertSessionHasErrors('password');
        $this->post(route('daftar.proses'), $this->isian(['email' => 'bukan-email']))->assertSessionHasErrors('email');
        $this->post(route('daftar.proses'), $this->isian(['setuju_tnc' => null]))->assertSessionHasErrors('setuju_tnc');
        $this->post(route('daftar.proses'), $this->isian(['peran' => 'tim_materi']))->assertSessionHasErrors('peran');
        $this->assertSame(0, AkunPengguna::count());
    }

    /** @uji C15-UT04 | Normal | simpanAkun(data: DataPendaftaran): HasilOperasi | Data Pelajar valid | Berhasil; sandi tersimpan sebagai hash bcrypt; baris motivasi_belajar dibuat */
    #[TestDox('C15-UT04 Normal: simpanAkun menyimpan hash dan baris motivasi')]
    public function test_ut04_simpan_akun_pelajar(): void
    {
        $hasil = $this->kontroler()->simpanAkun($this->isian());
        $akun = AkunPengguna::find($hasil->data['id_akun']);

        $this->assertTrue($hasil->berhasil);
        $this->assertNotSame('Rahasia123', $akun->password_hash);
        $this->assertTrue(Hash::check('Rahasia123', $akun->password_hash));
        $this->assertStringStartsWith('$2y$', $akun->password_hash);
        $this->assertSame(1, MotivasiBelajar::where('id_pelajar', $akun->id_akun)->count());
    }

    /** @uji C15-UT05 | Batas | simpanAkun(data: DataPendaftaran): HasilOperasi | Data Pengajar valid | Berhasil tanpa baris motivasi_belajar (motivasi hanya untuk Pelajar) */
    #[TestDox('C15-UT05 Batas: akun Pengajar tidak mendapat baris motivasi')]
    public function test_ut05_simpan_akun_pengajar(): void
    {
        $hasil = $this->kontroler()->simpanAkun($this->isian(['peran' => 'pengajar']));

        $this->assertTrue($hasil->berhasil);
        $this->assertSame(0, MotivasiBelajar::count());
    }

    /** @uji C15-UT06 | Negatif | simpanAkun(data: DataPendaftaran): HasilOperasi | Email yang sudah terdaftar | Gagal dengan kode EMAIL_TERDAFTAR; jumlah akun tetap */
    #[TestDox('C15-UT06 Negatif: simpanAkun dengan email terdaftar gagal')]
    public function test_ut06_simpan_akun_email_ganda(): void
    {
        $this->kontroler()->simpanAkun($this->isian());
        $hasil = $this->kontroler()->simpanAkun($this->isian(['username' => 'dewi_02']));

        $this->assertSame('EMAIL_TERDAFTAR', $hasil->kode);
        $this->assertSame(1, AkunPengguna::count());
    }

    /** @uji C15-UT07 | Normal | validasiLogin(email: String, password: String): HasilOperasi | Email dan sandi akun uji yang benar | Berhasil, data berisi akun yang sesuai */
    #[TestDox('C15-UT07 Normal: kredensial benar diterima')]
    public function test_ut07_login_benar(): void
    {
        $akun = $this->buatPelajar();
        $hasil = $this->kontroler()->validasiLogin($akun->email, 'Rahasia123!');

        $this->assertTrue($hasil->berhasil);
        $this->assertSame($akun->id_akun, $hasil->data['akun']->id_akun);
    }

    /** @uji C15-UT08 | Batas | validasiLogin(email: String, password: String): HasilOperasi | Email benar dengan huruf besar; sandi benar | Berhasil (email tidak peka huruf besar) */
    #[TestDox('C15-UT08 Batas: email huruf besar tetap dapat masuk')]
    public function test_ut08_login_email_huruf_besar(): void
    {
        $akun = $this->buatPelajar();

        $this->assertTrue($this->kontroler()->validasiLogin(strtoupper($akun->email), 'Rahasia123!')->berhasil);
    }

    /** @uji C15-UT09 | Negatif | validasiLogin(email: String, password: String): HasilOperasi | (a) sandi salah, (b) email tidak terdaftar | Keduanya gagal dengan kode dan pesan yang sama (KREDENSIAL_SALAH) agar tidak membocorkan email terdaftar */
    #[TestDox('C15-UT09 Negatif: sandi salah dan email tak terdaftar ditolak dengan pesan sama')]
    public function test_ut09_login_salah(): void
    {
        $akun = $this->buatPelajar();
        $a = $this->kontroler()->validasiLogin($akun->email, 'SalahSandi9');
        $b = $this->kontroler()->validasiLogin('tidak.ada@contoh.test', 'Rahasia123!');

        $this->assertSame('KREDENSIAL_SALAH', $a->kode);
        $this->assertSame('KREDENSIAL_SALAH', $b->kode);
        $this->assertSame($a->pesan, $b->pesan);
    }
}
