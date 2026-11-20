<?php

namespace Tests\Feature\Hiupl;

use App\Models\AkunPengguna;
use App\Models\MotivasiBelajar;
use Database\Seeders\MateriSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\Feature\BantuanData;
use Tests\Support\AlurLatihan;
use Tests\TestCase;
use Tests\Unit\Hiupl\BantuanMateri;

class IntegrasiTest extends TestCase
{
    use AlurLatihan, BantuanData, BantuanMateri, RefreshDatabase;

    #[TestDox('INT-TC01 Normal: daftar lalu masuk melalui seluruh lapisan')]
    public function test_tc01_daftar_lalu_masuk(): void
    {
        $this->post(route('daftar.proses'), ['peran' => 'pelajar', 'email' => 'rina@contoh.test', 'username' => 'rina_01', 'password' => 'Rahasia123', 'setuju_tnc' => '1'])
            ->assertSessionHasNoErrors();
        $akun = AkunPengguna::where('email', 'rina@contoh.test')->firstOrFail();
        $this->assertNotSame('Rahasia123', $akun->password_hash);
        $this->assertSame(1, MotivasiBelajar::where('id_pelajar', $akun->id_akun)->count());

        $this->post(route('keluar'));
        $this->post(route('masuk.proses'), ['email' => 'rina@contoh.test', 'password' => 'Rahasia123'])->assertRedirect(route('beranda'));
        $this->assertAuthenticatedAs($akun);
    }

    #[TestDox('INT-TC02 Negatif: masuk dengan sandi salah ditolak')]
    public function test_tc02_masuk_salah(): void
    {
        $p = $this->buatPelajar();

        $this->from(route('masuk'))->post(route('masuk.proses'), ['email' => $p->email, 'password' => 'Salah12345'])->assertRedirect(route('masuk'))->assertSessionHas('galat');
        $this->assertGuest();
    }

    #[TestDox('INT-TC03 Normal: daftar dan detail materi dari basis data')]
    public function test_tc03_materi(): void
    {
        $this->seed(MateriSeeder::class);
        $p = $this->buatPelajar();
        $id = DB::table('materi_aksara')->where('status_publikasi', 'terbit')->orderBy('id_materi')->value('id_materi');
        $nama = DB::table('materi_aksara')->where('id_materi', $id)->value('nama_materi');

        $this->actingAs($p)->get(route('materi.index', ['jenis' => 'jawa', 'tingkat' => 1]))->assertOk()->assertSee($nama);
        $this->get(route('materi.show', $id))->assertOk()->assertSee($nama);
    }

    #[TestDox('INT-TC04 Normal: unggah materi dengan audio sampai diputar Pelajar')]
    public function test_tc04_unggah_audio(): void
    {
        Storage::fake('local');
        $this->actingAs(AkunPengguna::factory()->timMateri()->create());
        $this->post(route('materi.simpan'), ['nama_materi' => 'Nglegena: ha na', 'jenis_aksara' => 'jawa', 'tingkat_kesulitan' => 1,
            'aturan_contoh' => 'Tulis dari kiri ke kanan. Contoh: ꦲꦤ = "ana"', 'berkas' => UploadedFile::fake()->create('ha.mp3', 30, 'audio/mpeg')])
            ->assertRedirect(route('materi.kelola'));
        $id = DB::table('materi_aksara')->value('id_materi');
        Storage::disk('local')->assertExists("materi/{$id}/audio.mp3");
        $this->post(route('materi.terbitkan', $id));

        $this->actingAs($this->buatPelajar())->get(route('materi.audio', $id))->assertOk();
    }

    #[TestDox('INT-TC05 Negatif: berkas tidak didukung tidak tersimpan')]
    public function test_tc05_unggah_ditolak(): void
    {
        Storage::fake('local');
        $this->actingAs(AkunPengguna::factory()->timMateri()->create());
        $this->post(route('materi.simpan'), ['nama_materi' => 'Nglegena: ha na', 'jenis_aksara' => 'jawa', 'tingkat_kesulitan' => 1,
            'aturan_contoh' => 'Contoh: ꦲꦤ = "ana"', 'berkas' => UploadedFile::fake()->create('x.exe', 5)])->assertSessionHasErrors('berkas');

        $this->assertSame(0, DB::table('materi_aksara')->count());
        $this->assertSame([], Storage::disk('local')->allFiles());
    }

    #[TestDox('INT-TC06 Normal: alur latihan menulis sampai progres')]
    public function test_tc06_latihan_sampai_progres(): void
    {
        $this->seed(MateriSeeder::class);
        $p = $this->buatPelajar();
        $soal = $this->jawabBenar($p);
        $this->postJson('/latihan/selesai', ['idSoal' => $soal->idSoal])->assertOk();

        $this->assertDatabaseHas('riwayat_latihan', ['id_pelajar' => $p->id_akun, 'status' => 'berhasil', 'mode' => 'menulis']);
        $this->get(route('latihan.hasil'))->assertOk()->assertSee('10');
        $this->get(route('progres.index'))->assertOk();
        $this->assertSame(10, MotivasiBelajar::milikPelajar($p->id_akun)->poin);
    }

    #[TestDox('INT-TC07 Normal: latihan bunyi tersimpan')]
    public function test_tc07_latihan_bunyi(): void
    {
        $this->seed(MateriSeeder::class);
        $p = $this->buatPelajar();
        $this->actingAs($p)->get(route('latihan.soal.mulai', ['bunyi', 1]))->assertOk();
        $sesi = session('latihan_non_kanvas');
        $token = array_key_first($sesi);
        $jawaban = [];
        foreach ($sesi[$token]['soal'] as $s) {
            $jawaban[$s['id']] = $s['kunci'];
        }
        $this->post(route('latihan.soal.kirim', 'bunyi'), ['token' => $token, 'jawaban' => $jawaban])->assertRedirect();

        $this->assertGreaterThan(0, DB::table('riwayat_latihan')->where('id_pelajar', $p->id_akun)->where('mode', 'bunyi')->where('status', 'berhasil')->count());
        $this->get(route('latihan.soal.hasil'))->assertOk();
    }

    #[TestDox('INT-TC08 Normal: feedback tersimpan lewat HTTP')]
    public function test_tc08_feedback(): void
    {
        $p = $this->buatPelajar();

        $this->actingAs($p)->post(route('feedback.kirim'), ['kategori' => 'fitur', 'pesan' => 'Tambahkan aksara Bali.'])
            ->assertRedirect(route('feedback.index'))->assertSessionHas('sukses');
        $this->assertDatabaseHas('data_feedback', ['id_akun' => $p->id_akun, 'status' => 'baru']);
    }

    #[TestDox('INT-TC09 Negatif: kegagalan simpan feedback tercatat di berkas log')]
    public function test_tc09_feedback_gagal_tercatat(): void
    {
        $log = storage_path('logs/hiupl-int.log');
        @unlink($log);
        config(['logging.default' => 'hiupl', 'logging.channels.hiupl' => ['driver' => 'single', 'path' => $log, 'level' => 'debug']]);
        $p = $this->buatPelajar();
        DB::statement('DROP TABLE data_feedback');

        $this->actingAs($p)->from(route('feedback.index'))->post(route('feedback.kirim'), ['kategori' => 'fitur', 'pesan' => 'Tambahkan aksara Bali.'])
            ->assertSessionHas('galat')->assertSessionHasInput('pesan');
        $this->assertFileExists($log);
        $this->assertStringContainsString('Feedback gagal disimpan', file_get_contents($log));
        @unlink($log);
    }

    #[TestDox('INT-TC10 Normal: alur kelas dari buat kelas sampai pengumpulan')]
    public function test_tc10_alur_kelas(): void
    {
        $g = $this->buatPengajar();
        $m = $this->materiLengkap();
        $this->actingAs($g)->post(route('pengajar.kelas.buat'), ['nama' => 'Kelas 7A']);
        $kelas = DB::table('kelas_belajar')->first();
        $this->post(route('pengajar.kelas.terbitkan', $kelas->id_kelas), ['instruksi' => 'Baca materi ꦲ', 'tenggat' => now()->addDay()->format('Y-m-d H:i'),
            'komponen' => [['materi' => $m->id_materi, 'jenis' => 'materi', 'wajib' => '1']]])->assertSessionHas('sukses');
        $tugas = DB::table('tugas_kelas')->first();
        $komponen = DB::table('komponen_tugas')->first();

        $p = $this->buatPelajar();
        $this->actingAs($p)->post(route('kelas-pelajar.gabung'), ['kode' => $kelas->kode_bergabung])->assertRedirect(route('kelas-pelajar.kelas', $kelas->id_kelas));
        $this->post(route('kelas-pelajar.selesai-materi', [$kelas->id_kelas, $tugas->id_tugas, $komponen->id_komponen]))->assertSessionHas('sukses');
        $this->post(route('kelas-pelajar.kumpulkan', [$kelas->id_kelas, $tugas->id_tugas]))->assertSessionHas('sukses');

        $this->assertDatabaseHas('penyelesaian_tugas', ['id_tugas' => $tugas->id_tugas, 'id_pelajar' => $p->id_akun, 'status' => 'tepat_waktu']);
    }

    #[TestDox('INT-TC11 Negatif: pengumpulan sebelum komponen selesai ditolak')]
    public function test_tc11_kumpul_belum_lengkap(): void
    {
        $g = $this->buatPengajar();
        $m = $this->buatMateri();
        $idKelas = $this->buatKelas($g);
        $p = $this->buatPelajar();
        $this->gabung($idKelas, $p);
        $idTugas = DB::table('tugas_kelas')->insertGetId(['id_kelas' => $idKelas, 'instruksi' => 'X', 'tenggat' => now()->addDay(), 'status' => 'terbit'], 'id_tugas');
        DB::table('komponen_tugas')->insert(['id_tugas' => $idTugas, 'jenis' => 'materi', 'id_materi' => $m->id_materi, 'mode' => null, 'wajib' => true]);

        $this->actingAs($p)->post(route('kelas-pelajar.kumpulkan', [$idKelas, $idTugas]))->assertSessionHasErrors('pengumpulan');
        $this->assertSame(0, DB::table('penyelesaian_tugas')->count());
    }

    #[TestDox('INT-TC12 Normal: pemantauan progres anggota')]
    public function test_tc12_pemantauan(): void
    {
        $g = $this->buatPengajar();
        $idKelas = $this->buatKelas($g);
        $p = $this->buatPelajar('Sari Wulan');
        $this->gabung($idKelas, $p);
        $this->riwayat($p, $this->buatMateri(), 85);

        $this->actingAs($g)->get(route('pemantauan.index', ['idKelas' => $idKelas, 'pelajar' => $p->id_akun]))->assertOk()->assertSee('Sari Wulan');
    }

    #[TestDox('INT-TC13 Negatif: rute dilindungi sesuai peran')]
    public function test_tc13_hak_akses(): void
    {
        $p = $this->buatPelajar();
        $idKelas = $this->buatKelas($this->buatPengajar());

        $this->get(route('progres.index'))->assertRedirect(route('masuk'));
        $this->actingAs($p)->get(route('materi.kelola'))->assertForbidden();
        $this->actingAs($p)->get(route('pengajar.kelas.index'))->assertForbidden();
        $this->actingAs($this->buatPengajar('Pak Lain'))->get(route('pemantauan.index', $idKelas))->assertForbidden();
    }

    #[TestDox('INT-TC14 Normal: papan peringkat kelas di halaman progres')]
    public function test_tc14_scoreboard(): void
    {
        $idKelas = $this->buatKelas($this->buatPengajar(), true);
        $a = $this->buatPelajar('Anisa');
        $b = $this->buatPelajar('Bayu');
        foreach ([[$a, 30], [$b, 10]] as [$x, $poin]) {
            $this->gabung($idKelas, $x);
            MotivasiBelajar::milikPelajar($x->id_akun)->tambahPoin(1, $poin);
        }

        $this->actingAs($a)->get(route('progres.index', ['kelas' => $idKelas]))->assertOk()->assertSeeInOrder(['Anisa', 'Bayu'])->assertDontSee($b->email);
    }
}
