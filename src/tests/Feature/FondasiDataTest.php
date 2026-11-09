<?php

namespace Tests\Feature;

use App\Models\AkunPengguna;
use App\Models\MotivasiBelajar;
use App\Models\RiwayatLatihan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * Pengujian fondasi data: seeder, kata sandi ter-hash, middleware peran, dan integrasi
 * dengan modul materi.
 */
class FondasiDataTest extends TestCase
{
    use BantuanData, RefreshDatabase;

    public function test_seeder_mengisi_akun_uji_dan_data_contoh(): void
    {
        $this->seed();

        $this->assertSame(7, AkunPengguna::count());
        $this->assertSame(4, MotivasiBelajar::count()); // satu baris untuk tiap Pelajar
        $this->assertSame(1, DB::table('keanggotaan_kelas')->where('status', 'keluar')->count());
        $this->assertSame(2, DB::table('komponen_tugas')->count());
        $this->assertGreaterThan(0, RiwayatLatihan::count());
        $this->assertGreaterThan(0, DB::table('materi_aksara')->where('status_publikasi', 'terbit')->count());
    }

    public function test_kata_sandi_disimpan_sebagai_hash(): void
    {
        $this->seed();
        $akun = AkunPengguna::query()->where('email', 'pelajar@ngaksara.test')->firstOrFail();

        $this->assertNotSame('Rahasia123!', $akun->password_hash);
        $this->assertTrue(Hash::check('Rahasia123!', $akun->password_hash));
        $this->assertArrayNotHasKey('password_hash', $akun->toArray());
    }

    public function test_middleware_peran_membatasi_akses(): void
    {
        Route::middleware(['web', 'auth', 'peran:pengajar'])->get('/uji-peran', fn () => 'boleh');

        $this->actingAs(AkunPengguna::factory()->pengajar()->create())->get('/uji-peran')->assertOk();
        $this->actingAs(AkunPengguna::factory()->create())->get('/uji-peran')->assertForbidden();
    }

    public function test_tamu_diarahkan_ke_halaman_awal_selama_login_belum_ada(): void
    {
        Route::middleware(['web', 'auth'])->get('/uji-tamu', fn () => 'rahasia');

        $this->get('/uji-tamu')->assertRedirect(route('beranda'));
    }

    public function test_daftar_materi_menampilkan_status_progres_dari_riwayat(): void
    {
        $pelajar = $this->buatPelajar();
        $materi = $this->buatMateri('Nglegena: ha na ca ra ka');
        $this->riwayat($pelajar, $materi, 95);

        $respon = $this->actingAs($pelajar)->get('/materi')->assertOk();

        // MateriController memanggil RiwayatLatihan::ambilStatusProgres untuk I-04
        $this->assertSame(['status' => 'selesai', 'persen' => 95], RiwayatLatihan::ambilStatusProgres($pelajar->id_akun)[$materi->id_materi]);
        $respon->assertSee('Nglegena: ha na ca ra ka');
    }
}
