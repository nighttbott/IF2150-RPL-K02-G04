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

/**
 * HIUPL 4.3: pengujian integrasi antarkomponen. Semua komponen yang terlibat adalah komponen nyata
 * (route + middleware + controller + model + basis data + penyimpanan berkas + log), tanpa mock.
 * Format anotasi: @uji ID | Jenis | antarmuka yang diuji | prosedur | hasil yang diharapkan
 */
class IntegrasiTest extends TestCase
{
    use AlurLatihan, BantuanData, BantuanMateri, RefreshDatabase;

    /** @uji INT-TC01 | Normal | HalamanPendaftaran → OtentikasiController → AkunPengguna → Basis Data (akun_pengguna, motivasi_belajar) | POST /daftar data Pelajar valid, lalu POST /masuk dengan kredensial yang sama | Akun dan baris motivasi tersimpan; sandi ter-hash; login berhasil dan diarahkan ke beranda */
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

    /** @uji INT-TC02 | Negatif | HalamanLogin → OtentikasiController → AkunPengguna → Basis Data | POST /masuk dengan sandi salah | Kembali ke formulir dengan galat; pengguna tetap tamu (UC01 Alternatif 1) */
    #[TestDox('INT-TC02 Negatif: masuk dengan sandi salah ditolak')]
    public function test_tc02_masuk_salah(): void
    {
        $p = $this->buatPelajar();

        $this->from(route('masuk'))->post(route('masuk.proses'), ['email' => $p->email, 'password' => 'Salah12345'])->assertRedirect(route('masuk'))->assertSessionHas('galat');
        $this->assertGuest();
    }

    /** @uji INT-TC03 | Normal | HalamanMateri → MateriController → MateriAksara + RiwayatLatihan → Basis Data | Pelajar (data MateriSeeder) membuka GET /materi?jenis=jawa&tingkat=1 lalu GET /materi/{id} | Daftar menampilkan materi terbit; detail menampilkan isi materi */
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

    /** @uji INT-TC04 | Normal | HalamanKelolaMateri → MateriController → MateriAksara + KontenMateri → Basis Data + PenyimpananBerkas | Tim Materi POST /kelola/materi dengan berkas mp3, terbitkan, lalu Pelajar GET /materi/{id}/audio | Materi, konten, dan berkas tersimpan; audio dapat diputar (200) */
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

    /** @uji INT-TC05 | Negatif | HalamanKelolaMateri → MateriController → PenyimpananBerkas | POST /kelola/materi dengan berkas .exe | Galat "Jenis berkas tidak didukung"; tidak ada materi dan berkas tersimpan (UC06 Alternatif 1) */
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

    /** @uji INT-TC06 | Normal | HalamanLatihan → LatihanController → AkurasiGoresan → RiwayatLatihan + MotivasiBelajar → Basis Data | Buka soal menulis, kirim goresan benar, POST /latihan/selesai, buka /latihan/hasil dan /progres | Riwayat berhasil tersimpan; poin 10, streak 1; halaman hasil dan progres menampilkan data yang sama */
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

    /** @uji INT-TC07 | Normal | HalamanLatihan (non-kanvas) → LatihanNonKanvasController → PenilaiJawaban → RiwayatLatihan → Basis Data | Mulai latihan bunyi, jawab semua soal dengan kunci yang benar | Halaman hasil tampil; riwayat mode bunyi tersimpan */
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

    /** @uji INT-TC08 | Normal | HalamanFeedback → FeedbackController → DataFeedback → Basis Data | POST /feedback kategori fitur | Redirect dengan pesan "Feedback terkirim"; baris data_feedback status baru */
    #[TestDox('INT-TC08 Normal: feedback tersimpan lewat HTTP')]
    public function test_tc08_feedback(): void
    {
        $p = $this->buatPelajar();

        $this->actingAs($p)->post(route('feedback.kirim'), ['kategori' => 'fitur', 'pesan' => 'Tambahkan aksara Bali.'])
            ->assertRedirect(route('feedback.index'))->assertSessionHas('sukses');
        $this->assertDatabaseHas('data_feedback', ['id_akun' => $p->id_akun, 'status' => 'baru']);
    }

    /** @uji INT-TC09 | Negatif | FeedbackController → DataFeedback → Basis Data → LogAplikasi | Tabel data_feedback tidak tersedia saat POST /feedback | Isian dipertahankan dengan pesan gagal; galat tertulis di berkas log aplikasi (UC09 Alternatif 1, KNF01) */
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

    /** @uji INT-TC10 | Normal | HalamanKelasPelajar → KelasPelajarController → KeanggotaanKelas + TugasKelas + PengumpulanTugas + PenyelesaianTugas → Basis Data | Pengajar membuat kelas dan menerbitkan tugas (komponen materi), Pelajar bergabung dengan kode, menyelesaikan komponen, lalu mengumpulkan | Keanggotaan aktif; penyelesaian_tugas berstatus tepat_waktu */
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

    /** @uji INT-TC11 | Negatif | HalamanKelasPelajar → KelasPelajarController → PengumpulanTugas → Basis Data | Pelajar mengumpulkan tugas sebelum komponen wajib selesai | Galat pengumpulan; tidak ada baris penyelesaian_tugas */
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

    /** @uji INT-TC12 | Normal | HalamanKelolaKelas → PemantauanKelasController → ProgresController → RiwayatLatihan → Basis Data | Pengajar membuka GET /pemantauan/{idKelas}?pelajar={id} untuk anggota yang punya riwayat | 200; nama anggota dan ringkasan progres tampil */
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

    /** @uji INT-TC13 | Negatif | Middleware CekPeran → Controller (hak akses lintas peran) | Pelajar membuka /kelola/materi dan /pengajar/kelas; Pengajar membuka /pemantauan kelas milik Pengajar lain; tamu membuka /progres | 403, 403, 403, dialihkan ke /masuk */
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

    /** @uji INT-TC14 | Normal | HalamanProgres → ProgresController → MotivasiBelajar (scoreboard) → Basis Data | Dua anggota kelas dengan poin 30 dan 10, scoreboard aktif; anggota membuka GET /progres?kelas={id} | Papan peringkat tampil dengan urutan poin; email tidak tampil */
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
