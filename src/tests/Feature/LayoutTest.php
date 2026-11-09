<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class LayoutTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Route stub: agar Route::has() di sidebar bernilai true saat tes.
        $rute = [
            'beranda'       => '/tes/beranda',
            'materi.index'  => '/tes/materi',
            'materi.show'   => '/tes/materi/1',
            'materi.kelola' => '/tes/materi/kelola',
            'materi.buat'   => '/tes/materi/buat',
            'latihan.index' => '/tes/latihan',
            'progres'       => '/tes/progres',
            'kelas.index'   => '/tes/kelas',
            'kelas.kelola'  => '/tes/kelas/kelola',
            'kelas.pantau'  => '/tes/kelas/pantau',
            'feedback'      => '/tes/feedback',
        ];

        foreach ($rute as $nama => $path) {
            Route::get($path, fn () => 'stub')->name($nama);
        }

        app('router')->getRoutes()->refreshNameLookups();

        Route::get('/tes-layout', fn () => view('layouts.app'))->middleware('web');
    }

    private function userDenganPeran(string $peran, string $username): User
    {
        $user = new User();
        $user->username = $username;
        $user->peran = $peran;

        return $user;
    }

    public function test_tim_materi_melihat_menu_materi(): void
    {
        $user = $this->userDenganPeran('tim_materi', 'mas_bayu');

        $this->withoutVite()
            ->withoutExceptionHandling()
            ->actingAs($user)
            ->get('/tes-layout')
            ->assertOk()
            ->assertSee('Kelola Materi')
            ->assertSee('Unggah Materi')
            ->assertDontSee('Latihan');
    }

    public function test_pelajar_melihat_menu_belajar(): void
    {
        $user = $this->userDenganPeran('pelajar', 'sari');

        $this->withoutVite()
            ->withoutExceptionHandling()
            ->actingAs($user)
            ->get('/tes-layout')
            ->assertOk()
            ->assertSee('Latihan')
            ->assertSee('Progres')
            ->assertDontSee('Kelola Materi');
    }

    public function test_peran_tidak_dikenal_tidak_menampilkan_menu(): void
    {
        $user = $this->userDenganPeran('tamu', 'x');

        $this->withoutVite()
            ->withoutExceptionHandling()
            ->actingAs($user)
            ->get('/tes-layout')
            ->assertOk()
            ->assertDontSee('Kelola Materi')
            ->assertDontSee('Latihan')
            ->assertDontSee('Progres');
    }
}