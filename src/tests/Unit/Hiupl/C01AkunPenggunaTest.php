<?php

namespace Tests\Unit\Hiupl;

use App\Enums\Peran;
use App\Models\AkunPengguna;
use App\Support\DataPendaftaran;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\Feature\BantuanData;
use Tests\TestCase;

/**
 * HIUPL 4.2: pengujian unit kelas C01 AkunPengguna (tingkat Mendalam: keamanan kredensial dan hak akses).
 * Setiap test memuat anotasi @uji: ID | jenis kasus | operasi | prosedur/masukan | hasil yang diharapkan.
 */
class C01AkunPenggunaTest extends TestCase
{
    use BantuanData, RefreshDatabase;

    private function data(string $email = 'budi@contoh.test', string $username = 'budi', ?string $nama = 'Budi'): DataPendaftaran
    {
        return new DataPendaftaran($email, $username, Hash::make('Rahasia123'), Peran::Pelajar, $nama);
    }

    /** @uji C01-UT01 | Normal | buatAkunBaru(data: DataPendaftaran) | Data pendaftaran baru (email budi@contoh.test, username budi, peran pelajar) | Akun tersimpan di akun_pengguna dan hasil berhasil berisi id_akun */
    #[TestDox('C01-UT01 Normal: buatAkunBaru menyimpan akun baru')]
    public function test_ut01_buat_akun_baru_tersimpan(): void
    {
        $akun = new AkunPengguna;
        $hasil = $akun->buatAkunBaru($this->data());

        $this->assertTrue($hasil->berhasil);
        $this->assertSame($akun->id_akun, $hasil->data['id_akun']);
        $this->assertDatabaseHas('akun_pengguna', ['email' => 'budi@contoh.test', 'username' => 'budi', 'peran' => 'pelajar']);
    }

    /** @uji C01-UT02 | Batas | buatAkunBaru(data: DataPendaftaran) | Email "  Budi@Contoh.TEST  " (huruf besar dan spasi) dan nama tampilan kosong | Email disimpan dalam huruf kecil tanpa spasi; nama tampilan diisi username */
    #[TestDox('C01-UT02 Batas: email dinormalisasi dan nama tampilan bawaan')]
    public function test_ut02_email_dinormalisasi(): void
    {
        $akun = new AkunPengguna;
        $hasil = $akun->buatAkunBaru($this->data('  Budi@Contoh.TEST  ', 'budi', null));

        $this->assertTrue($hasil->berhasil);
        $this->assertDatabaseHas('akun_pengguna', ['email' => 'budi@contoh.test', 'nama_tampilan' => 'budi']);
    }

    /** @uji C01-UT03 | Negatif | buatAkunBaru(data: DataPendaftaran) | Email yang sudah terdaftar dipakai lagi dengan username lain | Gagal dengan kode EMAIL_TERDAFTAR; tidak ada akun baru */
    #[TestDox('C01-UT03 Negatif: email terdaftar ditolak')]
    public function test_ut03_email_ganda_ditolak(): void
    {
        (new AkunPengguna)->buatAkunBaru($this->data());
        $hasil = (new AkunPengguna)->buatAkunBaru($this->data('budi@contoh.test', 'budi2'));

        $this->assertFalse($hasil->berhasil);
        $this->assertSame('EMAIL_TERDAFTAR', $hasil->kode);
        $this->assertSame(1, AkunPengguna::count());
    }

    /** @uji C01-UT04 | Negatif | buatAkunBaru(data: DataPendaftaran) | Username yang sudah dipakai didaftarkan dengan email lain | Gagal dengan kode USERNAME_TERPAKAI */
    #[TestDox('C01-UT04 Negatif: username terpakai ditolak')]
    public function test_ut04_username_ganda_ditolak(): void
    {
        (new AkunPengguna)->buatAkunBaru($this->data());
        $hasil = (new AkunPengguna)->buatAkunBaru($this->data('lain@contoh.test', 'budi'));

        $this->assertSame('USERNAME_TERPAKAI', $hasil->kode);
    }

    /** @uji C01-UT05 | Normal | cariBerdasarkanEmail(email: String): AkunPengguna? | Email akun yang terdaftar | Mengembalikan akun yang sesuai */
    #[TestDox('C01-UT05 Normal: cariBerdasarkanEmail menemukan akun')]
    public function test_ut05_cari_email_ditemukan(): void
    {
        $akun = $this->buatPelajar();

        $this->assertSame($akun->id_akun, AkunPengguna::cariBerdasarkanEmail($akun->email)?->id_akun);
    }

    /** @uji C01-UT06 | Batas | cariBerdasarkanEmail(email: String): AkunPengguna? | Email terdaftar ditulis dengan huruf besar dan spasi di awal/akhir | Akun tetap ditemukan */
    #[TestDox('C01-UT06 Batas: pencarian email tidak peka huruf besar dan spasi')]
    public function test_ut06_cari_email_huruf_besar(): void
    {
        $akun = $this->buatPelajar();

        $this->assertSame($akun->id_akun, AkunPengguna::cariBerdasarkanEmail('  '.strtoupper($akun->email).' ')?->id_akun);
    }

    /** @uji C01-UT07 | Negatif | cariBerdasarkanEmail(email: String): AkunPengguna? | Email yang tidak terdaftar | Mengembalikan null */
    #[TestDox('C01-UT07 Negatif: email tidak terdaftar menghasilkan null')]
    public function test_ut07_cari_email_tidak_ada(): void
    {
        $this->assertNull(AkunPengguna::cariBerdasarkanEmail('tidak.ada@contoh.test'));
    }

    /** @uji C01-UT08 | Normal | cocokkanPassword(password: String): Boolean | Kata sandi yang benar untuk akun uji | true */
    #[TestDox('C01-UT08 Normal: kata sandi benar cocok')]
    public function test_ut08_password_benar(): void
    {
        $this->assertTrue($this->buatPelajar()->cocokkanPassword('Rahasia123!'));
    }

    /** @uji C01-UT09 | Batas | cocokkanPassword(password: String): Boolean | String kosong dan kata sandi benar dengan beda satu huruf kapital | Keduanya false */
    #[TestDox('C01-UT09 Batas: kata sandi kosong atau beda satu huruf ditolak')]
    public function test_ut09_password_kosong_atau_beda_huruf(): void
    {
        $akun = $this->buatPelajar();

        $this->assertFalse($akun->cocokkanPassword(''));
        $this->assertFalse($akun->cocokkanPassword('rahasia123!'));
    }

    /** @uji C01-UT10 | Negatif | cocokkanPassword(password: String): Boolean | Kata sandi salah | false; hash tersimpan bukan teks asli */
    #[TestDox('C01-UT10 Negatif: kata sandi salah ditolak')]
    public function test_ut10_password_salah(): void
    {
        $akun = $this->buatPelajar();

        $this->assertFalse($akun->cocokkanPassword('SalahSandi9'));
        $this->assertNotSame('Rahasia123!', $akun->password_hash);
    }

    /** @uji C01-UT11 | Normal | verifikasiHakAkses(aksi: String): Boolean | Pelajar meminta MENGERJAKAN_LATIHAN; Pengajar meminta KELOLA_KELAS | Keduanya true */
    #[TestDox('C01-UT11 Normal: aksi sesuai peran diizinkan')]
    public function test_ut11_hak_akses_sesuai_peran(): void
    {
        $this->assertTrue($this->buatPelajar()->verifikasiHakAkses('MENGERJAKAN_LATIHAN'));
        $this->assertTrue($this->buatPengajar()->verifikasiHakAkses('KELOLA_KELAS'));
    }

    /** @uji C01-UT12 | Batas | verifikasiHakAkses(aksi: String): Boolean | Tim Materi meminta KELOLA_MATERI (satu-satunya hak) dan KIRIM_FEEDBACK | KELOLA_MATERI true, KIRIM_FEEDBACK false */
    #[TestDox('C01-UT12 Batas: Tim Materi hanya punya hak kelola materi')]
    public function test_ut12_hak_tim_materi(): void
    {
        $tim = AkunPengguna::factory()->timMateri()->create();

        $this->assertTrue($tim->verifikasiHakAkses('KELOLA_MATERI'));
        $this->assertFalse($tim->verifikasiHakAkses('KIRIM_FEEDBACK'));
    }

    /** @uji C01-UT13 | Negatif | verifikasiHakAkses(aksi: String): Boolean | Pelajar meminta KELOLA_KELAS; akun yang belum tersimpan meminta MENGERJAKAN_LATIHAN | Keduanya false */
    #[TestDox('C01-UT13 Negatif: aksi di luar peran dan akun belum tersimpan ditolak')]
    public function test_ut13_hak_akses_ditolak(): void
    {
        $this->assertFalse($this->buatPelajar()->verifikasiHakAkses('KELOLA_KELAS'));
        $this->assertFalse((new AkunPengguna(['peran' => 'pelajar']))->verifikasiHakAkses('MENGERJAKAN_LATIHAN'));
    }

    /** @uji C01-UT14 | Normal | getProfil(): Profil | Akun Pelajar "Sari" | Berisi id_akun, username, nama_tampilan, peran */
    #[TestDox('C01-UT14 Normal: getProfil mengembalikan data profil')]
    public function test_ut14_get_profil(): void
    {
        $p = $this->buatPelajar('Sari');

        $this->assertSame(['id_akun', 'username', 'nama_tampilan', 'peran'], array_keys($p->getProfil()));
        $this->assertSame('Sari', $p->getProfil()['nama_tampilan']);
    }

    /** @uji C01-UT15 | Batas | getProfil(): Profil | Akun dengan nama tampilan sama dengan username (nama tidak diisi saat daftar) | Profil memakai username sebagai nama tampilan */
    #[TestDox('C01-UT15 Batas: nama tampilan bawaan muncul di profil')]
    public function test_ut15_profil_nama_bawaan(): void
    {
        $akun = new AkunPengguna;
        $akun->buatAkunBaru($this->data('x@contoh.test', 'tanpa_nama', null));

        $this->assertSame('tanpa_nama', $akun->getProfil()['nama_tampilan']);
    }

    /** @uji C01-UT16 | Negatif | getProfil(): Profil | Profil dipakai untuk tampilan publik (papan peringkat) | Tidak memuat email maupun password_hash */
    #[TestDox('C01-UT16 Negatif: profil tidak membocorkan email dan hash')]
    public function test_ut16_profil_tanpa_data_rahasia(): void
    {
        $profil = $this->buatPelajar()->getProfil();

        $this->assertArrayNotHasKey('email', $profil);
        $this->assertArrayNotHasKey('password_hash', $profil);
    }

    /** @uji C01-UT17 | Normal | getDetailAkun(): DetailAkun | Akun Pengajar | Profil ditambah email pemilik akun */
    #[TestDox('C01-UT17 Normal: getDetailAkun memuat email')]
    public function test_ut17_detail_akun(): void
    {
        $g = $this->buatPengajar();

        $this->assertSame($g->email, $g->getDetailAkun()['email']);
        $this->assertSame('pengajar', $g->getDetailAkun()['peran']->value ?? $g->getDetailAkun()['peran']);
    }

    /** @uji C01-UT18 | Batas | getDetailAkun(): DetailAkun | Akun dengan email yang didaftarkan berhuruf besar | Email pada detail sudah huruf kecil */
    #[TestDox('C01-UT18 Batas: email detail sudah dinormalisasi')]
    public function test_ut18_detail_email_kecil(): void
    {
        $akun = new AkunPengguna;
        $akun->buatAkunBaru($this->data('BESAR@Contoh.Test', 'besar'));

        $this->assertSame('besar@contoh.test', $akun->getDetailAkun()['email']);
    }

    /** @uji C01-UT19 | Negatif | getDetailAkun(): DetailAkun | Objek akun kosong (belum tersimpan) | id_akun dan email bernilai null, tanpa galat */
    #[TestDox('C01-UT19 Negatif: akun kosong menghasilkan detail null')]
    public function test_ut19_detail_kosong(): void
    {
        $d = (new AkunPengguna)->getDetailAkun();

        $this->assertNull($d['id_akun']);
        $this->assertNull($d['email']);
    }
}
