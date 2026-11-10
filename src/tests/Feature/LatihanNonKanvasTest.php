<?php

namespace Tests\Feature;

use App\Models\MateriAksara;
use App\Models\MotivasiBelajar;
use App\Services\Latihan\PenyusunSoal;
use App\Services\Latihan\SelesaikanLatihan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/** Pengujian mode latihan non-kanvas: bunyi, merangkai, transliterasi (UC-03, bagian Marvel). */
class LatihanNonKanvasTest extends TestCase
{
    use BantuanData, RefreshDatabase;

    private function materiBerKonten(string $status = 'terbit'): MateriAksara
    {
        $materi = $this->buatMateri('Nglegena: ha na ca ra ka', $status);
        $materi->konten()->create([
            'bentuk_aksara' => 'ꦲ ꦤ ꦕ ꦫ ꦏ',
            'aturan' => 'Tulis dari kiri ke kanan.',
            'contoh' => 'ꦲꦤ = "ana"; ꦕꦫ = "cara"',
        ]);

        return $materi;
    }

    private function sesi(MateriAksara $materi, int $idPelajar, string $mode, array $soal, array $tambahan = []): array
    {
        return ['latihan_non_kanvas' => ['tok' => $tambahan + [
            'mode' => $mode, 'id_materi' => $materi->id_materi, 'id_pelajar' => $idPelajar,
            'id_tugas' => null, 'id_komponen' => null, 'dibuat' => now()->timestamp, 'soal' => $soal,
        ]]];
    }

    private function soalBunyi(): array
    {
        return [
            ['id' => 's0', 'target' => 'ꦲ', 'bunyi' => 'ha', 'opsi' => [['id' => 'a', 'teks' => 'ꦤ'], ['id' => 'b', 'teks' => 'ꦲ']], 'kunci' => 'b'],
            ['id' => 's1', 'target' => 'ꦤ', 'bunyi' => 'na', 'opsi' => [['id' => 'a', 'teks' => 'ꦤ'], ['id' => 'b', 'teks' => 'ꦲ']], 'kunci' => 'a'],
        ];
    }

    public function test_penyusun_soal_tidak_membocorkan_kunci(): void
    {
        $materi = $this->materiBerKonten();
        $penyusun = new PenyusunSoal;

        foreach (['bunyi', 'merangkai', 'transliterasi'] as $nama) {
            $soal = $penyusun->susun($materi, \App\Enums\ModeLatihan::from($nama), 5);
            $this->assertNotEmpty($soal, $nama);
            foreach (PenyusunSoal::publik($soal) as $s) {
                $this->assertArrayNotHasKey('kunci', $s);
            }
        }
        foreach ([\App\Enums\ModeLatihan::Merangkai, \App\Enums\ModeLatihan::Bunyi] as $mode) {
            $this->assertArrayNotHasKey('target', PenyusunSoal::publik($penyusun->susun($materi, $mode, 5))[0], $mode->value);
        }
    }

    /** I-08 dan DPPL (Algo-021): soal berupa audio/bacaan, pilihan berupa aksara, kunci = aksara yang benar. */
    public function test_soal_bunyi_menawarkan_aksara_sebagai_pilihan(): void
    {
        $soal = (new PenyusunSoal)->susun($this->materiBerKonten(), \App\Enums\ModeLatihan::Bunyi, 5);

        $this->assertNotEmpty($soal);
        foreach ($soal as $s) {
            $this->assertNotSame('', $s['bunyi']);
            $this->assertGreaterThanOrEqual(2, count($s['opsi']));
            $this->assertLessThanOrEqual(4, count($s['opsi']));
            foreach ($s['opsi'] as $o) {
                $this->assertMatchesRegularExpression('/^\X$/u', $o['teks'], 'pilihan harus satu aksara, bukan teks latin');
            }
            $kunci = collect($s['opsi'])->firstWhere('id', $s['kunci']);
            $this->assertSame($s['target'], $kunci['teks']);
        }
    }

    public function test_halaman_bunyi_memuat_tombol_speaker_dan_tidak_membocorkan_jawaban(): void
    {
        $materi = $this->materiBerKonten();
        $pelajar = $this->buatPelajar();

        // Tanpa audio: tombol dinonaktifkan dan pelajar diberi tahu.
        $this->actingAs($pelajar)->get("/latihan/soal/bunyi/{$materi->id_materi}")
            ->assertOk()->assertSee('Putar suara')->assertSee('Audio pengucapan belum tersedia');

        // Dengan audio: tombol membawa URL audio materi.
        \Illuminate\Support\Facades\Storage::fake(config('ngaksara.materi.disk'));
        \Illuminate\Support\Facades\Storage::disk(config('ngaksara.materi.disk'))->put('materi/a.mp3', 'x');
        $materi->konten()->update(['path_audio' => 'materi/a.mp3']);
        $respons = $this->actingAs($pelajar)->get("/latihan/soal/bunyi/{$materi->id_materi}")->assertOk();
        $respons->assertSee(route('materi.audio', $materi->id_materi), false)->assertDontSee('Audio pengucapan belum tersedia');

        // Aksara jawaban benar hanya ada di sesi server; di HTML ia hanya muncul sebagai salah satu pilihan.
        $sesi = collect(session('latihan_non_kanvas'))->first();
        $respons->assertDontSee('name="kunci"', false)->assertDontSee('name="target"', false);
        foreach ($sesi['soal'] as $s) {
            $this->assertNotEmpty($s['kunci']);
        }
    }

    public function test_soal_bunyi_punya_pilihan_unik_dan_kunci_ada_di_pilihan(): void
    {
        $soal = (new PenyusunSoal)->susun($this->materiBerKonten(), \App\Enums\ModeLatihan::Bunyi, 5);

        foreach ($soal as $s) {
            $teks = array_column($s['opsi'], 'teks');
            $this->assertSame($teks, array_values(array_unique($teks)));
            $this->assertContains($s['kunci'], array_column($s['opsi'], 'id'));
        }
    }

    public function test_halaman_mulai_ditampilkan_dan_sesi_disimpan(): void
    {
        $materi = $this->materiBerKonten();
        $pelajar = $this->buatPelajar();

        foreach (['bunyi', 'merangkai', 'transliterasi'] as $mode) {
            $this->actingAs($pelajar)->get("/latihan/soal/$mode/{$materi->id_materi}")->assertOk()->assertSee('name="token"', false);
        }
    }

    public function test_materi_tanpa_konten_menampilkan_halaman_kosong(): void
    {
        $materi = $this->buatMateri();

        $this->actingAs($this->buatPelajar())->get("/latihan/soal/bunyi/{$materi->id_materi}")->assertOk()->assertSee('belum tersedia');
    }

    public function test_materi_draf_tidak_bisa_dilatih(): void
    {
        $materi = $this->materiBerKonten('draf');

        $this->actingAs($this->buatPelajar())->get("/latihan/soal/bunyi/{$materi->id_materi}")->assertNotFound();
    }

    public function test_pengajar_tidak_bisa_berlatih(): void
    {
        $materi = $this->materiBerKonten();

        $this->actingAs($this->buatPengajar())->get("/latihan/soal/bunyi/{$materi->id_materi}")->assertForbidden();
    }

    public function test_mode_menulis_bukan_urusan_controller_ini(): void
    {
        $materi = $this->materiBerKonten();

        $this->actingAs($this->buatPelajar())->get("/latihan/soal/menulis/{$materi->id_materi}")->assertNotFound();
    }

    public function test_jawaban_bunyi_dinilai_di_server_dan_memberi_poin_streak_badge(): void
    {
        $materi = $this->materiBerKonten();
        $pelajar = $this->buatPelajar();

        $this->withSession($this->sesi($materi, $pelajar->id_akun, 'bunyi', $this->soalBunyi()))
            ->actingAs($pelajar)
            ->post('/latihan/soal/bunyi', ['token' => 'tok', 'jawaban' => ['s0' => 'b', 's1' => 'b']])
            ->assertRedirect(route('latihan.soal.hasil'));

        $riwayat = DB::table('riwayat_latihan')->where('id_pelajar', $pelajar->id_akun)->orderBy('id_riwayat')->get();
        $this->assertCount(2, $riwayat);
        $this->assertSame('berhasil', $riwayat[0]->status);
        $this->assertSame('perlu_diulang', $riwayat[1]->status);
        $this->assertSame('bunyi', $riwayat[0]->mode);

        $motivasi = MotivasiBelajar::find($pelajar->id_akun);
        $this->assertSame(10, $motivasi->poin); // hanya soal yang berhasil
        $this->assertSame(1, $motivasi->streak);
        $this->assertContains('pemula', $motivasi->badge());
    }

    public function test_hasil_perlu_diulang_tidak_mengubah_streak_dan_poin(): void
    {
        $materi = $this->materiBerKonten();
        $pelajar = $this->buatPelajar();

        $this->withSession($this->sesi($materi, $pelajar->id_akun, 'bunyi', $this->soalBunyi()))
            ->actingAs($pelajar)
            ->post('/latihan/soal/bunyi', ['token' => 'tok', 'jawaban' => ['s0' => 'a', 's1' => 'b']]);

        $motivasi = MotivasiBelajar::find($pelajar->id_akun);
        $this->assertSame(0, $motivasi?->poin ?? 0);
        $this->assertSame(0, $motivasi?->streak ?? 0);
    }

    public function test_soal_yang_sama_tidak_memberi_poin_dua_kali(): void
    {
        $materi = $this->materiBerKonten();
        $pelajar = $this->buatPelajar();
        $benar = ['token' => 'tok', 'jawaban' => ['s0' => 'b', 's1' => 'a']];

        for ($i = 0; $i < 2; $i++) {
            $this->withSession($this->sesi($materi, $pelajar->id_akun, 'bunyi', $this->soalBunyi()))
                ->actingAs($pelajar)->post('/latihan/soal/bunyi', $benar);
        }

        $this->assertSame(20, MotivasiBelajar::find($pelajar->id_akun)->poin);
        $this->assertSame(4, DB::table('riwayat_latihan')->count());
    }

    public function test_sesi_hanya_bisa_dikirim_sekali(): void
    {
        $materi = $this->materiBerKonten();
        $pelajar = $this->buatPelajar();
        $kirim = ['token' => 'tok', 'jawaban' => ['s0' => 'b', 's1' => 'a']];

        $this->withSession($this->sesi($materi, $pelajar->id_akun, 'bunyi', $this->soalBunyi()))
            ->actingAs($pelajar)->post('/latihan/soal/bunyi', $kirim);
        $this->actingAs($pelajar)->post('/latihan/soal/bunyi', $kirim)->assertRedirect(route('materi.index'));

        $this->assertSame(2, DB::table('riwayat_latihan')->count());
    }

    public function test_sesi_milik_pelajar_lain_ditolak(): void
    {
        $materi = $this->materiBerKonten();
        $pemilik = $this->buatPelajar('A');
        $lain = $this->buatPelajar('B');

        $this->withSession($this->sesi($materi, $pemilik->id_akun, 'bunyi', $this->soalBunyi()))
            ->actingAs($lain)->post('/latihan/soal/bunyi', ['token' => 'tok', 'jawaban' => ['s0' => 'b']])
            ->assertRedirect(route('materi.index'));

        $this->assertSame(0, DB::table('riwayat_latihan')->count());
    }

    public function test_sesi_kedaluwarsa_ditolak(): void
    {
        $materi = $this->materiBerKonten();
        $pelajar = $this->buatPelajar();
        $lama = now()->subHours(3)->timestamp;

        $this->withSession($this->sesi($materi, $pelajar->id_akun, 'bunyi', $this->soalBunyi(), ['dibuat' => $lama]))
            ->actingAs($pelajar)->post('/latihan/soal/bunyi', ['token' => 'tok', 'jawaban' => ['s0' => 'b']])
            ->assertRedirect(route('materi.index'));

        $this->assertSame(0, DB::table('riwayat_latihan')->count());
    }

    public function test_merangkai_menandai_posisi_salah_dan_menolak_ubin_berulang(): void
    {
        $materi = $this->materiBerKonten();
        $pelajar = $this->buatPelajar();
        $soal = [['id' => 's0', 'target' => 'ꦲꦤꦕ', 'bunyi' => 'anaca', 'ubin' => [['id' => 't0', 'teks' => 'ꦤ'], ['id' => 't1', 'teks' => 'ꦲ'], ['id' => 't2', 'teks' => 'ꦕ']], 'kunci' => ['ꦲ', 'ꦤ', 'ꦕ']]];

        // t1, t0, t0: posisi 3 memakai ubin berulang sehingga salah
        $this->withSession($this->sesi($materi, $pelajar->id_akun, 'merangkai', $soal))
            ->actingAs($pelajar)
            ->post('/latihan/soal/merangkai', ['token' => 'tok', 'urutan' => ['s0' => ['t1', 't0', 't0']]])
            ->assertRedirect();

        $this->assertEqualsWithDelta(66.67, (float) DB::table('riwayat_latihan')->value('skor'), 0.01);
        $this->assertSame('perlu_diulang', DB::table('riwayat_latihan')->value('status'));
    }

    public function test_merangkai_benar_penuh_berhasil(): void
    {
        $materi = $this->materiBerKonten();
        $pelajar = $this->buatPelajar();
        $soal = [['id' => 's0', 'target' => 'ꦲꦤ', 'bunyi' => 'ana', 'ubin' => [['id' => 't0', 'teks' => 'ꦤ'], ['id' => 't1', 'teks' => 'ꦲ']], 'kunci' => ['ꦲ', 'ꦤ']]];

        $this->withSession($this->sesi($materi, $pelajar->id_akun, 'merangkai', $soal))
            ->actingAs($pelajar)->post('/latihan/soal/merangkai', ['token' => 'tok', 'urutan' => ['s0' => ['t1', 't0']]]);

        $this->assertSame('berhasil', DB::table('riwayat_latihan')->value('status'));
        $this->assertSame('merangkai', DB::table('riwayat_latihan')->value('mode'));
    }

    public function test_transliterasi_trim_dan_huruf_kecil(): void
    {
        $materi = $this->materiBerKonten();
        $pelajar = $this->buatPelajar();
        $soal = [['id' => 's0', 'target' => 'ꦲꦤ', 'kunci' => 'ana'], ['id' => 's1', 'target' => 'ꦕꦫ', 'kunci' => 'cara']];

        $this->withSession($this->sesi($materi, $pelajar->id_akun, 'transliterasi', $soal))
            ->actingAs($pelajar)->post('/latihan/soal/transliterasi', ['token' => 'tok', 'jawaban' => ['s0' => '  ANA ', 's1' => '']]);

        $this->assertSame(['berhasil', 'perlu_diulang'], DB::table('riwayat_latihan')->orderBy('id_riwayat')->pluck('status')->all());
    }

    public function test_halaman_hasil_tampil_setelah_kirim(): void
    {
        $materi = $this->materiBerKonten();
        $pelajar = $this->buatPelajar();

        $this->withSession($this->sesi($materi, $pelajar->id_akun, 'bunyi', $this->soalBunyi()))
            ->actingAs($pelajar)
            ->followingRedirects()
            ->post('/latihan/soal/bunyi', ['token' => 'tok', 'jawaban' => ['s0' => 'b', 's1' => 'a']])
            ->assertOk()->assertSee('Hasil Latihan Bunyi')->assertSee('+20 poin');
    }

    public function test_hasil_tanpa_data_kembali_ke_materi(): void
    {
        $this->actingAs($this->buatPelajar())->get('/latihan/soal/hasil')->assertRedirect(route('materi.index'));
    }

    // ---- integrasi dengan tugas kelas ----

    private function siapkanTugas(MateriAksara $materi, $pelajar, string $mode = 'bunyi', string $statusTugas = 'terbit', string $statusAnggota = 'aktif'): array
    {
        $idKelas = $this->buatKelas($this->buatPengajar());
        $this->gabung($idKelas, $pelajar, $statusAnggota);
        $idTugas = DB::table('tugas_kelas')->insertGetId(['id_kelas' => $idKelas, 'instruksi' => 'x', 'tenggat' => now()->addDay()->format('Y-m-d H:i:s'), 'status' => $statusTugas], 'id_tugas');
        $idKomponen = DB::table('komponen_tugas')->insertGetId(['id_tugas' => $idTugas, 'jenis' => 'latihan', 'id_materi' => $materi->id_materi, 'mode' => $mode, 'wajib' => true], 'id_komponen');

        return [$idTugas, $idKomponen];
    }

    public function test_sesi_lulus_dari_tugas_menjadi_bukti_komponen(): void
    {
        $materi = $this->materiBerKonten();
        $pelajar = $this->buatPelajar();
        [$idTugas, $idKomponen] = $this->siapkanTugas($materi, $pelajar);

        $this->actingAs($pelajar)->get("/latihan/soal/bunyi/{$materi->id_materi}?tugas=$idTugas&komponen=$idKomponen")->assertOk();

        $this->withSession($this->sesi($materi, $pelajar->id_akun, 'bunyi', $this->soalBunyi(), ['id_tugas' => $idTugas, 'id_komponen' => $idKomponen]))
            ->actingAs($pelajar)->post('/latihan/soal/bunyi', ['token' => 'tok', 'jawaban' => ['s0' => 'b', 's1' => 'a']]);

        $bukti = \App\Models\RiwayatLatihan::ambilBuktiPenyelesaian($pelajar->id_akun, $idTugas);
        $this->assertArrayHasKey($idKomponen, $bukti);
    }

    public function test_sesi_gagal_dari_tugas_bukan_bukti_komponen(): void
    {
        $materi = $this->materiBerKonten();
        $pelajar = $this->buatPelajar();
        [$idTugas, $idKomponen] = $this->siapkanTugas($materi, $pelajar);

        $this->withSession($this->sesi($materi, $pelajar->id_akun, 'bunyi', $this->soalBunyi(), ['id_tugas' => $idTugas, 'id_komponen' => $idKomponen]))
            ->actingAs($pelajar)->post('/latihan/soal/bunyi', ['token' => 'tok', 'jawaban' => ['s0' => 'b', 's1' => 'b']]); // rata-rata 50

        $this->assertSame([], \App\Models\RiwayatLatihan::ambilBuktiPenyelesaian($pelajar->id_akun, $idTugas));
    }

    public function test_komponen_tugas_kelas_lain_atau_tidak_cocok_ditolak(): void
    {
        $materi = $this->materiBerKonten();
        $pelajar = $this->buatPelajar();
        $orangLain = $this->buatPelajar('Lain');
        [$idTugas, $idKomponen] = $this->siapkanTugas($materi, $orangLain);          // pelajar bukan anggota
        [$tugasDraf, $komponenDraf] = $this->siapkanTugas($materi, $pelajar, 'bunyi', 'draf');
        [$tugasMode, $komponenMode] = $this->siapkanTugas($materi, $pelajar, 'merangkai');
        [$tugasKeluar, $komponenKeluar] = $this->siapkanTugas($materi, $pelajar, 'bunyi', 'terbit', 'keluar');

        $url = fn ($t, $k) => "/latihan/soal/bunyi/{$materi->id_materi}?tugas=$t&komponen=$k";
        $this->actingAs($pelajar)->get($url($idTugas, $idKomponen))->assertForbidden();
        $this->actingAs($pelajar)->get($url($tugasDraf, $komponenDraf))->assertForbidden();
        $this->actingAs($pelajar)->get($url($tugasMode, $komponenMode))->assertForbidden();
        $this->actingAs($pelajar)->get($url($tugasKeluar, $komponenKeluar))->assertForbidden();
    }

    public function test_selesaikan_latihan_atomik_bila_satu_hasil_tidak_valid(): void
    {
        $pelajar = $this->buatPelajar();
        $materi = $this->buatMateri();
        $baik = ['id_materi' => $materi->id_materi, 'target_soal' => 'ꦲ', 'mode' => 'bunyi', 'skor' => 100, 'status' => 'berhasil'];
        $rusak = ['id_materi' => $materi->id_materi, 'target_soal' => 'ꦤ', 'mode' => 'bunyi', 'skor' => 500, 'status' => 'berhasil'];

        $hasil = (new SelesaikanLatihan)->selesaikan($pelajar->id_akun, [$baik, $rusak]);

        $this->assertFalse($hasil->berhasil);
        $this->assertSame(0, DB::table('riwayat_latihan')->count()); // transaksi dibatalkan
    }

    public function test_badge_seratus_poin_dan_konsisten_7(): void
    {
        $pelajar = $this->buatPelajar();
        $materi = $this->buatMateri();
        MotivasiBelajar::query()->create(['id_pelajar' => $pelajar->id_akun, 'streak' => 6, 'poin' => 95, 'tanggal_aktif_terakhir' => today()->subDay()->toDateString(), 'daftar_badge' => []]);

        $hasil = (new SelesaikanLatihan)->selesaikan($pelajar->id_akun, [
            ['id_materi' => $materi->id_materi, 'target_soal' => 'ꦲ', 'mode' => 'bunyi', 'skor' => 100, 'status' => 'berhasil'],
        ]);

        $this->assertTrue($hasil->berhasil);
        $this->assertEqualsCanonicalizing(['pemula', 'konsisten_7', 'seratus_poin'], $hasil->ambil('badgeBaru'));
    }
}
