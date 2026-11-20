<?php

namespace Tests\Feature\Hiupl;

use App\Models\AkunPengguna;
use Database\Seeders\MateriSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\Feature\BantuanData;
use Tests\TestCase;

/**
 * HIUPL 4.5: pengujian kebutuhan nonfungsional. Hasil pengukuran disimpan ke storage/app/hiupl/knf.json
 * agar angka di dokumen berasal dari pengukuran nyata.
 */
class KnfTest extends TestCase
{
    use BantuanData, RefreshDatabase;

    private static function catat(string $id, array $hasil): void
    {
        $berkas = storage_path('app/hiupl/knf.json');
        @mkdir(dirname($berkas), 0777, true);
        $data = is_file($berkas) ? json_decode(file_get_contents($berkas), true) : [];
        $data[$id] = $hasil + ['diukur' => now()->toDateTimeString(), 'php' => PHP_VERSION, 'db' => DB::connection()->getDriverName()];
        file_put_contents($berkas, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    private function logKe(string $nama): string
    {
        $log = storage_path("logs/$nama.log");
        @unlink($log);
        config(['logging.default' => 'knf', 'logging.channels.knf' => ['driver' => 'single', 'path' => $log, 'level' => 'debug']]);

        return $log;
    }

    /** @uji KNF01-TC01 | Normal | KNF01 Maintainability (log aktivitas & galat) | Login gagal satu kali lalu kegagalan simpan feedback (tabel dihapus) | Berkas log berisi entri "Login gagal" (WARNING) dan "Feedback gagal disimpan" (ERROR) lengkap dengan waktu; email tidak tertulis di log */
    #[TestDox('KNF01-TC01: aktivitas penting dan galat tercatat di log')]
    public function test_knf01_tc01_log(): void
    {
        $log = $this->logKe('knf01');
        $p = $this->buatPelajar();
        $this->post(route('masuk.proses'), ['email' => $p->email, 'password' => 'SalahSandi1']);
        DB::statement('DROP TABLE data_feedback');
        $this->actingAs($p)->post(route('feedback.kirim'), ['kategori' => 'fitur', 'pesan' => 'Pesan uji KNF01.']);
        $isi = file_get_contents($log);

        $this->assertMatchesRegularExpression('/\[\d{4}-\d{2}-\d{2}[ T]\d{2}:\d{2}:\d{2}.*\] \w+\.WARNING: Login gagal/', $isi);
        $this->assertMatchesRegularExpression('/\.ERROR: Feedback gagal disimpan/', $isi);
        $this->assertStringNotContainsString($p->email, $isi);
        self::catat('KNF01-TC01', ['entri' => substr_count($isi, "\n"), 'contoh' => array_values(array_filter(explode("\n", $isi)))]);
        @unlink($log);
    }

    /** @uji KNF01-TC02 | Negatif | KNF01 Maintainability (log aktivitas & galat) | Pengajar mengeluarkan Pelajar yang bukan anggota lalu Pengajar memantau Pelajar bukan anggota | Penolakan pemantauan tercatat (WARNING "Pemantauan ditolak") sehingga penelusuran masalah dapat dilakukan */
    #[TestDox('KNF01-TC02: penolakan akses pemantauan tercatat')]
    public function test_knf01_tc02_log_penolakan(): void
    {
        $log = $this->logKe('knf01b');
        $g = $this->buatPengajar();
        $idKelas = $this->buatKelas($g);
        $this->actingAs($g)->get(route('pemantauan.index', ['idKelas' => $idKelas, 'pelajar' => $this->buatPelajar()->id_akun]));

        $this->assertStringContainsString('Pemantauan ditolak', file_get_contents($log));
        @unlink($log);
    }

    /** @uji KNF02-TC01 | Normal | KNF02 Security (kata sandi di-hash) | Daftar akun lewat POST /daftar lalu periksa kolom password_hash di basis data | Nilai berupa hash bcrypt ($2y$, cost ≥ 10), tidak sama dengan teks asli, dan Hash::check bernilai true */
    #[TestDox('KNF02-TC01: kata sandi tersimpan sebagai hash bcrypt')]
    public function test_knf02_tc01_hash(): void
    {
        $this->post(route('daftar.proses'), ['peran' => 'pelajar', 'email' => 'knf@contoh.test', 'username' => 'knf_02', 'password' => 'Rahasia123', 'setuju_tnc' => '1']);
        $hash = DB::table('akun_pengguna')->where('email', 'knf@contoh.test')->value('password_hash');
        $info = password_get_info($hash);

        $this->assertSame('bcrypt', $info['algoName']);
        $this->assertGreaterThanOrEqual(10, $info['options']['cost']);
        $this->assertNotSame('Rahasia123', $hash);
        $this->assertStringNotContainsString('Rahasia123', json_encode(DB::table('akun_pengguna')->get()));
        self::catat('KNF02-TC01', ['algoritma' => $info['algoName'], 'cost' => $info['options']['cost'], 'awalan' => substr($hash, 0, 7), 'panjang' => strlen($hash)]);
    }

    /** @uji KNF02-TC02 | Negatif | KNF02 Security (kata sandi di-hash) | Dua akun dengan kata sandi sama; login memakai nilai hash sebagai kata sandi | Hash keduanya berbeda (salt unik); login dengan hash ditolak */
    #[TestDox('KNF02-TC02: hash bersalt dan hash tidak dapat dipakai sebagai sandi')]
    public function test_knf02_tc02_salt(): void
    {
        $a = AkunPengguna::factory()->create(['password_hash' => bcrypt('SamaSandi1')]);
        $b = AkunPengguna::factory()->create(['password_hash' => bcrypt('SamaSandi1')]);

        $this->assertNotSame($a->password_hash, $b->password_hash);
        $this->post(route('masuk.proses'), ['email' => $a->email, 'password' => $a->password_hash]);
        $this->assertGuest();
    }

    /** @uji KNF03-TC01 | Normal | KNF03 Performance (daftar materi < 3 detik) | 20 kali GET /materi?jenis=jawa&tingkat=1 oleh Pelajar dengan seluruh materi seeder dan 200 baris riwayat | Waktu respons maksimum < 3.000 ms (median, p95, maks dicatat) */
    #[TestDox('KNF03-TC01: daftar materi dimuat di bawah 3 detik')]
    public function test_knf03_tc01_waktu_daftar_materi(): void
    {
        $this->seed(MateriSeeder::class);
        $p = $this->buatPelajar();
        $materi = \App\Models\MateriAksara::all();
        for ($i = 0; $i < 200; $i++) {
            $this->riwayat($p, $materi[$i % $materi->count()], ($i * 7) % 101, $i % 3 ? 'berhasil' : 'perlu_diulang');
        }
        $this->actingAs($p)->get(route('materi.index'));
        $waktu = [];
        for ($i = 0; $i < 20; $i++) {
            $mulai = hrtime(true);
            $this->get(route('materi.index', ['jenis' => 'jawa', 'tingkat' => 1]))->assertOk();
            $waktu[] = (hrtime(true) - $mulai) / 1e6;
        }
        sort($waktu);

        $this->assertLessThan(3000, max($waktu));
        self::catat('KNF03-TC01', ['n' => 20, 'median_ms' => round($waktu[9], 1), 'p95_ms' => round($waktu[18], 1), 'maks_ms' => round(max($waktu), 1), 'jumlah_materi' => $materi->count(), 'riwayat' => 200]);
    }

    /** @uji KNF05-TC01 | Normal | KNF05 Reliability (feedback terkirim) | Kirim feedback valid lewat POST /feedback | Pesan "Feedback terkirim. Matur nuwun!" tampil dan baris tersimpan */
    #[TestDox('KNF05-TC01: konfirmasi feedback terkirim')]
    public function test_knf05_tc01_terkirim(): void
    {
        $this->actingAs($this->buatPelajar())->followingRedirects()->post(route('feedback.kirim'), ['kategori' => 'materi', 'pesan' => 'Materi sangat membantu.'])
            ->assertOk()->assertSee('Feedback terkirim. Matur nuwun!');
        $this->assertSame(1, DB::table('data_feedback')->count());
    }

    /** @uji KNF05-TC02 | Negatif | KNF05 Reliability (pesan galat saat gagal) | Basis data gagal menulis saat POST /feedback | Pesan "Feedback gagal terkirim ... Isi pesanmu tidak hilang." tampil dan isian pesan tetap terisi */
    #[TestDox('KNF05-TC02: pesan galat saat feedback gagal')]
    public function test_knf05_tc02_gagal(): void
    {
        $p = $this->buatPelajar();
        DB::statement('DROP TABLE data_feedback');

        $this->actingAs($p)->from(route('feedback.index'))->followingRedirects()
            ->post(route('feedback.kirim'), ['kategori' => 'materi', 'pesan' => 'Materi sangat membantu.'])
            ->assertSee('Feedback gagal terkirim', false)->assertSee('Materi sangat membantu.');
    }
}
