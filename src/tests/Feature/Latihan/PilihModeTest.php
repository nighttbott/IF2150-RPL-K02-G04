<?php

namespace Tests\Feature\Latihan;

use App\Models\MateriAksara;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Database\Seeders\MateriSeeder;
use Tests\Feature\BantuanData;
use Tests\TestCase;

class PilihModeTest extends TestCase
{
    use BantuanData, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(MateriSeeder::class);
    }

    public function test_tamu_diarahkan_ke_halaman_awal(): void
    {
        $this->get('/latihan/1')->assertRedirect(route('beranda'));
    }

    public function test_tombol_mulai_latihan_di_detail_materi_menuju_i06(): void
    {
        $this->actingAs($this->buatPelajar())->get(route('materi.show', 1))->assertSee(route('latihan.mode', 1));
    }

    public function test_pengajar_ditolak(): void
    {
        $this->actingAs($this->buatPengajar())->get('/latihan/1')
            ->assertForbidden()->assertSee('Anda tidak memiliki akses ke halaman ini.');
        $this->actingAs($this->buatPengajar())->postJson('/latihan/jawaban', ['idSoal' => 'x', 'goresan' => [[['x' => 0, 'y' => 0]]]])
            ->assertForbidden()->assertJson(['message' => 'Anda tidak memiliki akses ke halaman ini.']);
    }

    public function test_pelajar_melihat_empat_kartu_dan_motivasi(): void
    {
        $pelajar = $this->buatPelajar();
        DB::table('motivasi_belajar')->insert([
            'id_pelajar' => $pelajar->id_akun, 'streak' => 5, 'poin' => 240,
            'tanggal_aktif_terakhir' => today()->toDateString(), 'daftar_badge' => '["pemula","konsisten_7","seratus_poin"]',
        ]);

        $this->actingAs($pelajar)->get('/latihan/1')->assertOk()
            ->assertSeeInOrder(['Menulis', 'Mencocokkan bunyi', 'Merangkai aksara', 'Transliterasi'])
            ->assertSee('Materi: Nglegena: ha na ca ra ka')
            ->assertSee(route('materi.show', 1))
            ->assertSee(route('latihan.soal', [1, 'menulis']))
            ->assertSee('Segera hadir')
            ->assertSeeInOrder(['5', 'hari', '240', 'poin', '3', 'badge']);
    }

    public function test_streak_terputus_ditampilkan_nol(): void
    {
        $pelajar = $this->buatPelajar();
        DB::table('motivasi_belajar')->insert([
            'id_pelajar' => $pelajar->id_akun, 'streak' => 5, 'poin' => 40,
            'tanggal_aktif_terakhir' => today()->subDays(3)->toDateString(), 'daftar_badge' => '[]',
        ]);

        $this->actingAs($pelajar)->get('/latihan/1')->assertSeeInOrder(['<b class="lt-sogan">0</b>hari'], false);
    }

    public function test_mode_di_luar_daftar_404(): void
    {
        $this->actingAs($this->buatPelajar())->get('/latihan/1/menggambar')->assertNotFound();
    }

    public function test_mode_belum_tersedia_422(): void
    {
        $this->actingAs($this->buatPelajar())->get('/latihan/1/transliterasi')
            ->assertStatus(422)->assertSee('Mode latihan tidak dikenal');
    }

    public function test_materi_tanpa_konten_kembali_dengan_pesan(): void
    {
        $pelajar = $this->buatPelajar();
        $id = MateriAksara::create(['nama_materi' => 'Materi tanpa konten', 'jenis_aksara' => 'jawa', 'tingkat_kesulitan' => 2, 'status_publikasi' => 'terbit'])->id_materi;

        $this->actingAs($pelajar)->get("/latihan/$id/menulis")
            ->assertRedirect(route('latihan.mode', $id))
            ->assertSessionHas('galat', 'Materi ini belum memiliki soal untuk mode yang dipilih');
        $this->actingAs($pelajar)->get("/latihan/$id")->assertSee('Materi ini belum memiliki soal untuk mode yang dipilih');
    }

    public function test_materi_tanpa_templat_tidak_bisa_latihan_menulis(): void
    {
        // Sandhangan swara di seeder berisi suku kata gabungan (mis. ꦲꦶ) yang tidak punya templat.
        $id = MateriAksara::where('nama_materi', 'Sandhangan swara')->value('id_materi');

        $this->actingAs($this->buatPelajar())->get("/latihan/$id/menulis")
            ->assertRedirect(route('latihan.mode', $id))
            ->assertSessionHas('galat', 'Materi ini belum memiliki soal untuk mode yang dipilih');
    }

    public function test_materi_draf_dan_tidak_ada_404(): void
    {
        $pelajar = $this->buatPelajar();
        $draf = MateriAksara::create(['nama_materi' => 'Draf', 'jenis_aksara' => 'jawa', 'tingkat_kesulitan' => 1, 'status_publikasi' => 'draf'])->id_materi;

        $this->actingAs($pelajar)->get("/latihan/$draf")->assertNotFound();
        $this->actingAs($pelajar)->get("/latihan/$draf/menulis")->assertNotFound();
        $this->actingAs($pelajar)->get('/latihan/999')->assertNotFound();
    }
}
