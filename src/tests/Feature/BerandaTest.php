<?php

namespace Tests\Feature;

use App\Enums\JenisAksara;
use App\Enums\StatusPublikasi;
use App\Http\Controllers\BerandaController;
use App\Models\AkunPengguna;
use App\Models\MateriAksara;
use App\Models\MotivasiBelajar;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/** Pengujian C07 HalamanUtama / Beranda (I-03); jalankan: php artisan test --filter=BerandaTest. */
class BerandaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    // Helper

    /** Materi tanpa basis data, untuk menguji aturan peta. */
    private function materi(int $id, string $nama, int $tingkat, string $jenis = 'jawa'): MateriAksara
    {
        return (new MateriAksara)->forceFill([
            'id_materi'         => $id,
            'nama_materi'       => $nama,
            'jenis_aksara'      => $jenis,
            'tingkat_kesulitan' => $tingkat,
        ]);
    }

    /** Materi tersimpan, untuk uji lewat HTTP. */
    private function buatMateri(string $nama, int $tingkat, string $status = 'terbit'): MateriAksara
    {
        return MateriAksara::query()->create([
            'nama_materi'       => $nama,
            'jenis_aksara'      => JenisAksara::from('jawa'),
            'tingkat_kesulitan' => $tingkat,
            'status_publikasi'  => StatusPublikasi::from($status),
        ]);
    }

    /** Keadaan semua simpul peta, berurutan dari bagian pertama. */
    private function keadaan(array $bagian): array
    {
        return array_column(array_merge(...array_column($bagian, 'langkah')), 'keadaan');
    }

    // 1. Akses dan pengalihan (Algo-007)

    public function test_tamu_diarahkan_ke_masuk(): void
    {
        $this->get('/')->assertRedirect(route('masuk'));
    }

    public function test_pelajar_membuka_beranda(): void
    {
        $this->actingAs(AkunPengguna::factory()->create())->get('/')
            ->assertOk()
            ->assertViewIs('beranda')
            ->assertViewHas('pelajar', true);
    }

    public function test_pengajar_diarahkan_ke_menu_pertama_yang_tersedia(): void
    {
        $respons = $this->actingAs(AkunPengguna::factory()->pengajar()->create())->get('/');
        $tujuan = collect(['pengajar.kelas.index', 'pemantauan.kelas', 'feedback.index'])
            ->first(fn ($nama) => Route::has($nama));

        $tujuan
            ? $respons->assertRedirect(route($tujuan))
            : $respons->assertOk()->assertViewHas('pelajar', false);
    }

    public function test_tim_materi_diarahkan_ke_menu_pertamanya(): void
    {
        $respons = $this->actingAs(AkunPengguna::factory()->timMateri()->create())->get('/');

        Route::has('materi.kelola')
            ? $respons->assertRedirect(route('materi.kelola'))
            : $respons->assertOk()->assertViewHas('pelajar', false);
    }

    // 2. Salam mengikuti waktu server (I-03-03)

    #[DataProvider('jamProvider')]
    public function test_salam_mengikuti_waktu(int $jam, string $harapan): void
    {
        $this->assertSame($harapan, BerandaController::salam(Carbon::create(2026, 11, 10, $jam, 0)));
    }

    public static function jamProvider(): array
    {
        return [
            'tengah malam' => [0, 'Sugeng dalu'],
            'jam 3'        => [3, 'Sugeng dalu'],
            'jam 4'        => [4, 'Sugeng enjang'],
            'jam 9'        => [9, 'Sugeng enjang'],
            'jam 10'       => [10, 'Sugeng siyang'],
            'jam 14'       => [14, 'Sugeng siyang'],
            'jam 15'       => [15, 'Sugeng sonten'],
            'jam 17'       => [17, 'Sugeng sonten'],
            'jam 18'       => [18, 'Sugeng dalu'],
            'jam 23'       => [23, 'Sugeng dalu'],
        ];
    }

    // 3. Data view untuk Pelajar

    public function test_pelajar_menerima_semua_data_beranda(): void
    {
        $this->actingAs(AkunPengguna::factory()->create())->get('/')
            ->assertViewHasAll([
                'akun', 'pelajar', 'salam', 'motivasi',
                'saran', 'saranBaru', 'lanjutkan', 'jalur', 'target', 'kelas', 'tips',
            ]);
    }

    public function test_tanpa_materi_terbit_kartu_belajar_kosong(): void
    {
        $respons = $this->actingAs(AkunPengguna::factory()->create())->get('/');

        $this->assertNull($respons->viewData('saran'));
        $this->assertNull($respons->viewData('lanjutkan'));
        $this->assertSame('jawa', $respons->viewData('jalur')['jenis']);
        $this->assertSame([], $respons->viewData('jalur')['bagian']);
    }

    public function test_pelajar_baru_mendapat_saran_dan_kalimat_coba(): void
    {
        $this->buatMateri('Nglegena: ha na ca ra ka', 1);
        $this->buatMateri('Nglegena: da ta sa wa la', 1);

        $respons = $this->actingAs(AkunPengguna::factory()->create())->get('/');

        $saran = $respons->viewData('saran');
        $lanjutkan = $respons->viewData('lanjutkan');

        $this->assertNotNull($saran);
        $this->assertTrue($respons->viewData('saranBaru'));
        $this->assertSame($saran->id_materi, $lanjutkan['materi']->id_materi);
        $this->assertSame(0, $lanjutkan['persen']);
        $respons->assertSee('Hari ini coba');
    }

    // 4. Ringkasan motivasi (I-03-06, Algo-032)

    public function test_pelajar_tanpa_baris_motivasi_mendapat_nol(): void
    {
        $this->actingAs(AkunPengguna::factory()->create())->get('/')
            ->assertViewHas('motivasi', ['streak' => 0, 'poin' => 0, 'badge' => 0]);
    }

    public function test_ringkasan_motivasi_dari_ambil_motivasi(): void
    {
        $akun = AkunPengguna::factory()->create();
        MotivasiBelajar::query()->create([
            'id_pelajar'             => $akun->id_akun,
            'streak'                 => 5,
            'poin'                   => 240,
            'tanggal_aktif_terakhir' => now()->toDateString(),
            'daftar_badge'           => ['pemula', 'konsisten_7', 'seratus_poin'],
        ]);

        $this->actingAs($akun)->get('/')
            ->assertViewHas('motivasi', ['streak' => 5, 'poin' => 240, 'badge' => 3]);
    }

    public function test_streak_terputus_ditampilkan_nol_tetapi_poin_tetap(): void
    {
        $akun = AkunPengguna::factory()->create();
        MotivasiBelajar::query()->create([
            'id_pelajar'             => $akun->id_akun,
            'streak'                 => 5,
            'poin'                   => 240,
            'tanggal_aktif_terakhir' => now()->subDays(3)->toDateString(),
            'daftar_badge'           => [],
        ]);

        $this->actingAs($akun)->get('/')
            ->assertViewHas('motivasi', ['streak' => 0, 'poin' => 240, 'badge' => 0]);
    }

    // 5. Aturan peta belajar tanpa basis data (I-03-05, Algo-041)

    public function test_jalur_sesuai_data_layar(): void
    {
        // Urutan masukan meniru ambilDaftarMateri (menurut nama).
        $semua = [
            $this->materi(2, 'Nglegena: da ta sa wa la', 1),
            $this->materi(1, 'Nglegena: ha na ca ra ka', 1),
            $this->materi(4, 'Nglegena: ma ga ba tha nga', 1),
            $this->materi(3, 'Nglegena: pa dha ja ya nya', 1),
            $this->materi(5, 'Sandhangan swara', 2),
        ];

        $bagian = BerandaController::susunJalur($semua, [1 => 61.0, 2 => 55.26]);

        $this->assertSame([1, 2], array_column($bagian, 'tingkat'));
        $this->assertSame([1, 2, 3, 4], array_column($bagian[0]['langkah'], 'id_materi'));
        $this->assertSame(['mulai', 'terbuka', 'terbuka', 'terbuka', 'terkunci'], $this->keadaan($bagian));
    }

    public function test_tingkat_berikutnya_terbuka_tepat_di_ambang(): void
    {
        $bagian = BerandaController::susunJalur(
            [$this->materi(1, 'A', 1), $this->materi(2, 'B', 2)],
            [1 => 70.0],
        );

        $this->assertSame(['selesai', 'mulai'], $this->keadaan($bagian));
    }

    public function test_tingkat_tetap_terkunci_di_bawah_ambang(): void
    {
        $bagian = BerandaController::susunJalur(
            [$this->materi(1, 'A', 1), $this->materi(2, 'B', 2)],
            [1 => 69.99],
        );

        $this->assertSame(['mulai', 'terkunci'], $this->keadaan($bagian));
    }

    public function test_tingkat_ketiga_terkunci_bila_tingkat_kedua_belum_tuntas(): void
    {
        $bagian = BerandaController::susunJalur(
            [$this->materi(1, 'A', 1), $this->materi(2, 'B', 2), $this->materi(3, 'C', 3)],
            [1 => 90.0, 2 => 40.0],
        );

        $this->assertSame(['selesai', 'mulai', 'terkunci'], $this->keadaan($bagian));
    }

    public function test_semua_selesai_tidak_ada_simpul_mulai(): void
    {
        $bagian = BerandaController::susunJalur(
            [$this->materi(1, 'A', 1), $this->materi(2, 'B', 1)],
            [1 => 80.0, 2 => 100.0],
        );

        $this->assertSame(['selesai', 'selesai'], $this->keadaan($bagian));
    }

    public function test_tanpa_materi_jalur_kosong(): void
    {
        $this->assertSame([], BerandaController::susunJalur([], []));
    }

    public function test_ambang_mengikuti_konfigurasi(): void
    {
        config(['ngaksara.materi.ambang_tuntas' => 80]);

        $bagian = BerandaController::susunJalur(
            [$this->materi(1, 'A', 1), $this->materi(2, 'B', 2)],
            [1 => 75.0],
        );

        $this->assertSame(['mulai', 'terkunci'], $this->keadaan($bagian));
    }

    public function test_sudah_tuntas_memakai_ambang(): void
    {
        $m = $this->materi(1, 'A', 1);

        $this->assertFalse(MateriAksara::sudahTuntas($m, []));
        $this->assertFalse(MateriAksara::sudahTuntas($m, [1 => 69.99]));
        $this->assertTrue(MateriAksara::sudahTuntas($m, [1 => 70.0]));
    }

    public function test_kunci_tingkat_terpisah_per_jenis_aksara(): void
    {
        $jawa1 = $this->materi(1, 'Jawa 1', 1, 'jawa');
        $jawa2 = $this->materi(2, 'Jawa 2', 2, 'jawa');
        $sunda1 = $this->materi(3, 'Sunda 1', 1, 'sunda');
        $sunda2 = $this->materi(4, 'Sunda 2', 2, 'sunda');
        $semua = [$jawa1, $jawa2, $sunda1, $sunda2];

        // Sunda tingkat 1 tuntas, Jawa tingkat 1 belum.
        $penguasaan = [3 => 85.0];

        $this->assertTrue(MateriAksara::tingkatTerbuka($sunda2, $semua, $penguasaan));
        $this->assertFalse(MateriAksara::tingkatTerbuka($jawa2, $semua, $penguasaan));
    }

    // 6. Peta belajar lewat HTTP

    public function test_peta_dari_basis_data_untuk_pelajar_baru(): void
    {
        $hana = $this->buatMateri('Nglegena: ha na ca ra ka', 1);
        $data = $this->buatMateri('Nglegena: da ta sa wa la', 1);
        $this->buatMateri('Sandhangan swara', 2);
        $this->buatMateri('Pasangan (draf)', 1, 'draf');

        $jalur = $this->actingAs(AkunPengguna::factory()->create())->get('/')->viewData('jalur');

        $this->assertSame('jawa', $jalur['jenis']);
        $this->assertSame([1, 2], array_column($jalur['bagian'], 'tingkat'));
        $this->assertSame(
            [$hana->id_materi, $data->id_materi],
            array_column($jalur['bagian'][0]['langkah'], 'id_materi'),
            'Urutan harus menurut id_materi, dan materi draf tidak ikut.',
        );
        $this->assertSame(['mulai', 'terbuka', 'terkunci'], $this->keadaan($jalur['bagian']));
    }

    // 7. Target harian (I-03-07)

    public function test_target_harian_bawaan(): void
    {
        $this->actingAs(AkunPengguna::factory()->create())->get('/')
            ->assertViewHas('target', ['selesai' => 0, 'target' => 3]);
    }

    public function test_target_harian_mengikuti_konfigurasi(): void
    {
        config(['ngaksara.target_harian' => 5]);

        $this->actingAs(AkunPengguna::factory()->create())->get('/')
            ->assertViewHas('target', ['selesai' => 0, 'target' => 5]);
    }

    public function test_target_harian_minimal_satu(): void
    {
        config(['ngaksara.target_harian' => 0]);

        $this->actingAs(AkunPengguna::factory()->create())->get('/')
            ->assertViewHas('target', ['selesai' => 0, 'target' => 1]);
    }

    // 8. Kartu Kelasmu dan Kata Pak Aksa

    public function test_pelajar_tanpa_kelas_mendapat_daftar_kosong(): void
    {
        $this->actingAs(AkunPengguna::factory()->create())->get('/')
            ->assertViewHas('kelas', []);
    }

    public function test_kartu_kelas_menghitung_tugas_yang_belum_dikumpulkan(): void
    {
        $pelajar = AkunPengguna::factory()->create();
        $guru = AkunPengguna::factory()->pengajar()->create();
        $kelas = \DB::table('kelas_belajar')->insertGetId(['nama' => 'Jawa Dasar', 'pemilik_id' => $guru->id_akun, 'kode_bergabung' => 'ABC234', 'status' => 'aktif', 'status_scoreboard' => false], 'id_kelas');
        \DB::table('keanggotaan_kelas')->insert(['id_kelas' => $kelas, 'id_pelajar' => $pelajar->id_akun, 'status' => 'aktif']);
        $tugas = fn (string $status) => \DB::table('tugas_kelas')->insertGetId(['id_kelas' => $kelas, 'instruksi' => 'x', 'tenggat' => now()->addDay(), 'status' => $status], 'id_tugas');
        $sudah = $tugas('terbit');
        $tugas('terbit');
        $tugas('draf');
        \DB::table('penyelesaian_tugas')->insert(['id_tugas' => $sudah, 'id_pelajar' => $pelajar->id_akun, 'waktu_kumpul' => now(), 'status' => 'tepat_waktu']);

        $this->actingAs($pelajar)->get('/')
            ->assertViewHas('kelas', [['id_kelas' => $kelas, 'nama' => 'Jawa Dasar', 'tugas_menunggu' => 1]])
            ->assertSee('1 tugas menunggu')
            ->assertSee(route('kelas-pelajar.kelas', $kelas), false);
    }

    public function test_tips_selalu_berisi(): void
    {
        $this->actingAs(AkunPengguna::factory()->create())->get('/')
            ->assertViewHas('tips', fn ($tips) => is_string($tips) && $tips !== '');
    }
}