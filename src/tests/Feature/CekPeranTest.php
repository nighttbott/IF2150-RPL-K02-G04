<?php

namespace Tests\Feature;

use App\Models\AkunPengguna;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class CekPeranTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite(); // halaman 403 memakai layout yang memuat @vite

        Route::middleware(['web', 'auth', 'peran:pelajar'])->get('/uji/pelajar', fn () => 'ok');
        Route::middleware(['web', 'auth', 'peran:pelajar,pengajar'])->get('/uji/dua-peran', fn () => 'ok');
    }

    public function test_peran_yang_sesuai_diizinkan(): void
    {
        $this->actingAs(AkunPengguna::factory()->create())
            ->get('/uji/pelajar')->assertOk();
    }

    public function test_peran_lain_ditolak_dengan_403(): void
    {
        $this->actingAs(AkunPengguna::factory()->pengajar()->create())
            ->get('/uji/pelajar')->assertForbidden();

        $this->actingAs(AkunPengguna::factory()->timMateri()->create())
            ->get('/uji/pelajar')->assertForbidden();
    }

    public function test_beberapa_peran_dalam_satu_aturan(): void
    {
        $this->actingAs(AkunPengguna::factory()->create())->get('/uji/dua-peran')->assertOk();
        $this->actingAs(AkunPengguna::factory()->pengajar()->create())->get('/uji/dua-peran')->assertOk();
        $this->actingAs(AkunPengguna::factory()->timMateri()->create())->get('/uji/dua-peran')->assertForbidden();
    }

    public function test_tamu_diarahkan_ke_masuk(): void
    {
        $this->get('/uji/pelajar')->assertRedirect(route('masuk'));
    }

    public function test_permintaan_json_dari_tamu_mendapat_401(): void
    {
        $this->getJson('/uji/pelajar')->assertUnauthorized();
    }
}