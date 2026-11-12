<?php

namespace Tests\Feature;

use App\Models\AkunPengguna;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class DaftarTest extends TestCase
{
    use RefreshDatabase;

    private function data(array $ubah = []): array
    {
        return array_merge([
            'peran'           => 'pelajar',
            'email'           => 'sari@contoh.test',
            'username'        => 'sari_belajar',
            'password'        => 'Rahasia123',
            'persetujuan_tnc' => '1',
        ], $ubah);
    }

    public function test_halaman_daftar_tampil_untuk_tamu(): void
    {
        $this->get(route('daftar'))
            ->assertOk()
            ->assertSee('Buat akun baru')
            ->assertSee('Pelajar')
            ->assertSee('Pengajar')
            ->assertSee('Syarat');
    }

    public function test_pengguna_yang_sudah_masuk_tidak_bisa_membuka_halaman_daftar(): void
    {
        $this->actingAs(AkunPengguna::factory()->create())
            ->get(route('daftar'))
            ->assertRedirect();
    }

    public function test_pelajar_berhasil_mendaftar(): void
    {
        $this->post(route('daftar.proses'), $this->data())
            ->assertRedirect(route('masuk'))
            ->assertSessionHas('sukses', 'Akun berhasil dibuat. Silakan masuk.');

        $this->assertDatabaseHas('akun_pengguna', [
            'email'         => 'sari@contoh.test',
            'username'      => 'sari_belajar',
            'peran'         => 'pelajar',
            'nama_tampilan' => 'sari_belajar',
        ]);
        $this->assertGuest(); // pendaftaran tidak langsung masuk
    }

    public function test_pengajar_berhasil_mendaftar(): void
    {
        $this->post(route('daftar.proses'), $this->data(['peran' => 'pengajar']))
            ->assertRedirect(route('masuk'));

        $this->assertDatabaseHas('akun_pengguna', ['email' => 'sari@contoh.test', 'peran' => 'pengajar']);
    }

    public function test_sandi_tersimpan_sebagai_hash_bcrypt_cost_12(): void
    {
        $this->post(route('daftar.proses'), $this->data());

        $akun = AkunPengguna::cariBerdasarkanEmail('sari@contoh.test');

        $this->assertNotSame('Rahasia123', $akun->password_hash);
        $this->assertTrue(Hash::check('Rahasia123', $akun->password_hash));
        $this->assertSame(12, Hash::info($akun->password_hash)['options']['cost']);
    }

    public function test_akun_baru_bisa_langsung_masuk(): void
    {
        $this->post(route('daftar.proses'), $this->data());

        $this->post(route('masuk.proses'), ['email' => 'sari@contoh.test', 'password' => 'Rahasia123'])
            ->assertRedirect();
        $this->assertAuthenticated();
    }

    public function test_baris_motivasi_dibuat_untuk_pelajar_saja(): void
    {
        if (! Schema::hasTable('motivasi_belajar')) {
            $this->markTestSkipped('Tabel motivasi_belajar belum ada.');
        }

        $this->post(route('daftar.proses'), $this->data());
        $this->post(route('daftar.proses'), $this->data([
            'peran' => 'pengajar', 'email' => 'bu@contoh.test', 'username' => 'bu_rina',
        ]));

        $pelajar = AkunPengguna::cariBerdasarkanEmail('sari@contoh.test');
        $pengajar = AkunPengguna::cariBerdasarkanEmail('bu@contoh.test');

        $this->assertDatabaseHas('motivasi_belajar', ['id_pelajar' => $pelajar->id_akun]);
        $this->assertDatabaseMissing('motivasi_belajar', ['id_pelajar' => $pengajar->id_akun]);
    }

    public function test_akun_dibatalkan_bila_baris_motivasi_gagal_dibuat(): void
    {
        if (! Schema::hasTable('motivasi_belajar')) {
            $this->markTestSkipped('Tabel motivasi_belajar belum ada.');
        }
        Schema::drop('motivasi_belajar'); // memaksa pembuatan baris motivasi gagal

        $this->post(route('daftar.proses'), $this->data())
            ->assertSessionHas('galat_daftar.kode', 'SIMPAN_GAGAL');

        $this->assertDatabaseCount('akun_pengguna', 0);
    }

    #[DataProvider('isianTidakValid')]
    public function test_isian_tidak_valid_ditolak(array $ubah, string $kolomGalat): void
    {
        $this->post(route('daftar.proses'), $this->data($ubah))
            ->assertSessionHasErrors($kolomGalat);

        $this->assertDatabaseCount('akun_pengguna', 0);
    }

    public static function isianTidakValid(): array
    {
        return [
            'peran tim materi'      => [['peran' => 'tim_materi'], 'peran'],
            'peran asing'           => [['peran' => 'admin'], 'peran'],
            'peran kosong'          => [['peran' => ''], 'peran'],
            'email bukan email'     => [['email' => 'bukan-email'], 'email'],
            'email terlalu panjang' => [['email' => str_repeat('a', 251).'@c.id'], 'email'],
            'username terlalu pendek' => [['username' => 'ab'], 'username'],
            'username terlalu panjang' => [['username' => str_repeat('a', 31)], 'username'],
            'username berspasi'     => [['username' => 'sari belajar'], 'username'],
            'username berkarakter aneh' => [['username' => 'sari-belajar!'], 'username'],
            'sandi terlalu pendek'  => [['password' => 'Abc123'], 'password'],
            'sandi tanpa angka'     => [['password' => 'RahasiaSaja'], 'password'],
            'sandi tanpa huruf'     => [['password' => '12345678'], 'password'],
            'tnc tidak dicentang'   => [['persetujuan_tnc' => null], 'persetujuan_tnc'],
        ];
    }

    public function test_semua_galat_dikembalikan_sekaligus(): void
    {
        $this->post(route('daftar.proses'), [])
            ->assertSessionHasErrors(['peran', 'email', 'username', 'password', 'persetujuan_tnc']);
    }

    public function test_pesan_galat_sesuai(): void
    {
        $this->post(route('daftar.proses'), $this->data(['password' => 'abc', 'setuju_tnc' => null]))
            ->assertSessionHasErrors([
                'password'        => 'Kata sandi minimal 8 karakter dan memuat huruf serta angka',
                'setuju_tnc' => 'Terms & Conditions belum disetujui',
            ]);
    }

    public function test_email_duplikat_ditolak_tanpa_peka_huruf_besar(): void
    {
        AkunPengguna::factory()->create(['email' => 'sari@contoh.test']);

        $this->post(route('daftar.proses'), $this->data(['email' => 'SARI@Contoh.test', 'username' => 'lain_lagi']))
            ->assertSessionHas('galat_daftar.kode', 'EMAIL_TERDAFTAR')
            ->assertSessionHas('galat_daftar.pesan', 'Email sudah terdaftar');

        $this->assertDatabaseCount('akun_pengguna', 1);
    }

    public function test_username_duplikat_ditolak(): void
    {
        AkunPengguna::factory()->create(['username' => 'sari_belajar']);

        $this->post(route('daftar.proses'), $this->data(['email' => 'lain@contoh.test']))
            ->assertSessionHas('galat_daftar.kode', 'USERNAME_TERPAKAI');

        $this->assertDatabaseCount('akun_pengguna', 1);
    }

    public function test_isian_dipertahankan_dan_sandi_tidak_kembali_saat_gagal(): void
    {
        AkunPengguna::factory()->create(['email' => 'sari@contoh.test']);

        $this->from(route('daftar'))
            ->post(route('daftar.proses'), $this->data(['username' => 'lain_lagi']))
            ->assertRedirect(route('daftar'))
            ->assertSessionHasInput('email', 'sari@contoh.test')
            ->assertSessionHasInput('username', 'lain_lagi')
            ->assertSessionMissing('_old_input.password');
    }
}