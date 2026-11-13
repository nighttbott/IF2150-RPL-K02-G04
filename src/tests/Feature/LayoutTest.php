<?php

namespace Tests\Feature;

use App\Models\AkunPengguna;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Sidebar per peran (I-03-01) dan profil + keluar (I-03-02); diadaptasi dari feat/akun-layout (Ryuza). */
class LayoutTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_pelajar_melihat_menu_belajar_dan_profil(): void
    {
        $sari = AkunPengguna::factory()->create(['nama_tampilan' => 'Sari']);

        $this->actingAs($sari)->get(route('feedback.index'))->assertOk()
            ->assertSeeInOrder(['Beranda', 'Materi', 'Latihan', 'Progres', 'Kelas', 'Feedback'])
            ->assertSee(route('latihan.index'), false)
            ->assertSee(route('kelas-pelajar.index'), false)
            ->assertSee('Pelajar')->assertSee('Sari')
            ->assertSee(route('keluar'), false)
            ->assertDontSee('Kelola Materi')->assertDontSee('Kelola Kelas');
    }

    public function test_pengajar_melihat_menu_kelas_dan_pemantauan(): void
    {
        $guru = AkunPengguna::factory()->pengajar()->create(['nama_tampilan' => 'Bu Rina']);

        $this->actingAs($guru)->get(route('feedback.index'))->assertOk()
            ->assertSeeInOrder(['Beranda', 'Kelola Kelas', 'Pantau Progres', 'Feedback'])
            ->assertSee(route('pengajar.kelas.index'), false)
            ->assertSee(route('pemantauan.kelas'), false)
            ->assertDontSee(route('latihan.index'), false)
            ->assertDontSee('Kelola Materi');
    }

    public function test_tim_materi_melihat_menu_materi(): void
    {
        $tim = AkunPengguna::factory()->timMateri()->create(['nama_tampilan' => 'Mas Bayu']);

        $this->actingAs($tim)->get(route('materi.kelola'))->assertOk()
            ->assertSee('Kelola Materi')->assertSee('Unggah Materi')
            ->assertSee(route('materi.buat'), false)
            ->assertDontSee(route('latihan.index'), false)
            ->assertDontSee(route('feedback.index'), false);
    }

    public function test_menu_aktif_ditandai(): void
    {
        $sari = AkunPengguna::factory()->create();

        $html = $this->actingAs($sari)->get(route('kelas-pelajar.index'))->assertOk()->getContent();

        $this->assertMatchesRegularExpression('#href="'.preg_quote(route('kelas-pelajar.index'), '#').'"\s+class="aktif"\s+aria-current="page"#', $html);
        $this->assertDoesNotMatchRegularExpression('#href="'.preg_quote(route('progres.index'), '#').'"\s+class="aktif"#', $html);
    }

    public function test_url_tim_materi_tidak_menimpa_beranda(): void
    {
        $this->assertSame(url('/kelola/materi'), route('materi.kelola'));
        $this->assertSame(url('/'), route('beranda'));
    }
}
