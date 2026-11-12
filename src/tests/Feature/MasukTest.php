<?php

namespace Tests\Feature;

use App\Models\AkunPengguna;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class MasukTest extends TestCase
{
    use RefreshDatabase;

    private const SANDI = 'Rahasia123!'; // sandi bawaan AkunPenggunaFactory

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    public function test_halaman_masuk_tampil_untuk_tamu(): void
    {
        $this->get('/masuk')
            ->assertOk()
            ->assertSee('Masuk')
            ->assertDontSee('Kelola Materi');
    }

    public function test_tamu_yang_membuka_halaman_terlindungi_diarahkan_ke_masuk(): void
    {
        $this->get('/materi')->assertRedirect(route('masuk'));
    }

    public function test_berhasil_masuk_untuk_setiap_peran(): void
    {
        foreach (['pelajar', 'pengajar', 'tim_materi'] as $peran) {
            $akun = AkunPengguna::factory()->create(['peran' => $peran]);

            $this->post('/masuk', ['email' => $akun->email, 'password' => self::SANDI])
                ->assertRedirect();
            $this->assertAuthenticatedAs($akun);

            $this->post('/keluar');
            $this->assertGuest();
        }
    }

    public function test_email_tanpa_huruf_besar_dan_spasi_tetap_diterima(): void
    {
        AkunPengguna::factory()->create(['email' => 'budi@contoh.test']);

        $this->post('/masuk', ['email' => ' BUDI@Contoh.test ', 'password' => self::SANDI])
            ->assertRedirect();
        $this->assertAuthenticated();
    }

    public function test_sandi_salah_dan_email_tidak_terdaftar_mendapat_pesan_sama(): void
    {
        AkunPengguna::factory()->create(['email' => 'budi@contoh.test']);

        $this->post('/masuk', ['email' => 'budi@contoh.test', 'password' => 'salah'])
            ->assertSessionHas('galat', 'Email atau kata sandi salah');

        $this->post('/masuk', ['email' => 'tidak@ada.test', 'password' => 'salah'])
            ->assertSessionHas('galat', 'Email atau kata sandi salah');

        $this->assertGuest();
    }

    public function test_email_tetap_terisi_saat_gagal(): void
    {
        AkunPengguna::factory()->create(['email' => 'budi@contoh.test']);

        $this->from('/masuk')
            ->post('/masuk', ['email' => 'budi@contoh.test', 'password' => 'salah'])
            ->assertSessionHasInput('email', 'budi@contoh.test');
    }

    public function test_percobaan_keenam_terkunci_meski_sandi_benar(): void
    {
        $akun = AkunPengguna::factory()->create();

        for ($i = 0; $i < 5; $i++) {
            $this->post('/masuk', ['email' => $akun->email, 'password' => 'salah']);
        }

        $this->post('/masuk', ['email' => $akun->email, 'password' => self::SANDI])
            ->assertSessionHas('tunggu');
        $this->assertGuest();
    }

    public function test_keluar_mengakhiri_sesi(): void
    {
        $akun = AkunPengguna::factory()->create();

        $this->actingAs($akun)->post('/keluar')->assertRedirect(route('masuk'));
        $this->assertGuest();
    }

    public function test_keluar_hanya_menerima_post(): void
    {
        $this->actingAs(AkunPengguna::factory()->create())
            ->get('/keluar')
            ->assertStatus(405);
    }
}