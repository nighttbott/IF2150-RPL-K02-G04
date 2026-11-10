<?php

namespace Tests\Feature\Kelas;

use App\Enums\Peran;
use App\Models\AkunPengguna;
use App\Support\DataPendaftaran;
use App\Support\HasilOperasi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AkunPenggunaTest extends TestCase
{
    use RefreshDatabase;

    private const SANDI = 'Rahasia123!'; // sandi bawaan AkunPenggunaFactory

    private function sukses(HasilOperasi $hasil): bool
    {
        return $hasil->berhasil;
    }

    private function kode(HasilOperasi $hasil): ?string
    {
        return $hasil->kode;
    }

    private function dataHasil(HasilOperasi $hasil): array
    {
        return $hasil->data;
    }

    private function pendaftaran(array $ubah = []): DataPendaftaran
    {
        return new DataPendaftaran(...array_merge([
            'email'        => 'Sari@Contoh.test ',
            'username'     => 'sari_belajar',
            'passwordHash' => Hash::make(self::SANDI),
            'peran'        => Peran::Pelajar,
            'namaTampilan' => null,
        ], $ubah));
    }

    // ---------------------------------------------------------------- buatAkunBaru 

    public function test_akun_baru_tersimpan_dengan_email_ternormalisasi(): void
    {
        $akun = new AkunPengguna();
        $hasil = $akun->buatAkunBaru($this->pendaftaran());

        $this->assertTrue($this->sukses($hasil), 'Gagal dengan kode: ' . $this->kode($hasil));
        $this->assertSame($akun->id_akun, $this->dataHasil($hasil)['id_akun']);
        $this->assertSame('sari@contoh.test', $akun->email);
        $this->assertDatabaseHas('akun_pengguna', [
            'email'    => 'sari@contoh.test',
            'username' => 'sari_belajar',
            'peran'    => Peran::Pelajar->value,
        ]);
    }

    public function test_sandi_tersimpan_sebagai_hash(): void
    {
        $akun = new AkunPengguna();
        $akun->buatAkunBaru($this->pendaftaran());

        $this->assertNotSame(self::SANDI, $akun->password_hash);
        $this->assertTrue($akun->fresh()->cocokkanPassword(self::SANDI));
    }

    public function test_nama_tampilan_kosong_diisi_username(): void
    {
        $akun = new AkunPengguna();
        $akun->buatAkunBaru($this->pendaftaran());

        $this->assertSame('sari_belajar', $akun->nama_tampilan);
    }

    public function test_nama_tampilan_yang_diberikan_dipakai(): void
    {
        $akun = new AkunPengguna();
        $akun->buatAkunBaru($this->pendaftaran(['namaTampilan' => 'Sari']));

        $this->assertSame('Sari', $akun->nama_tampilan);
    }

    public function test_email_duplikat_ditolak_tanpa_peka_huruf_besar_dan_spasi(): void
    {
        (new AkunPengguna())->buatAkunBaru($this->pendaftaran());

        $hasil = (new AkunPengguna())->buatAkunBaru(
            $this->pendaftaran(['email' => ' SARI@contoh.TEST', 'username' => 'lain'])
        );

        $this->assertFalse($this->sukses($hasil));
        $this->assertSame('EMAIL_TERDAFTAR', $this->kode($hasil));
        $this->assertDatabaseCount('akun_pengguna', 1);
    }

    public function test_username_duplikat_ditolak(): void
    {
        (new AkunPengguna())->buatAkunBaru($this->pendaftaran());

        $hasil = (new AkunPengguna())->buatAkunBaru(
            $this->pendaftaran(['email' => 'lain@contoh.test'])
        );

        $this->assertFalse($this->sukses($hasil));
        $this->assertSame('USERNAME_TERPAKAI', $this->kode($hasil));
        $this->assertDatabaseCount('akun_pengguna', 1);
    }

    public function test_email_diperiksa_lebih_dulu_bila_keduanya_duplikat(): void
    {
        (new AkunPengguna())->buatAkunBaru($this->pendaftaran());

        $hasil = (new AkunPengguna())->buatAkunBaru($this->pendaftaran());

        $this->assertSame('EMAIL_TERDAFTAR', $this->kode($hasil));
    }

    // ---------------------------------------------------------------- cari dan cocokkan

    public function test_cari_berdasarkan_email_tidak_peka_huruf_besar(): void
    {
        $akun = AkunPengguna::factory()->create(['email' => 'budi@contoh.test']);

        $this->assertSame($akun->id_akun, AkunPengguna::cariBerdasarkanEmail(' Budi@Contoh.test ')->id_akun);
        $this->assertNull(AkunPengguna::cariBerdasarkanEmail('tidak-ada@contoh.test'));
    }

    public function test_cocokkan_password(): void
    {
        $akun = AkunPengguna::factory()->create();

        $this->assertTrue($akun->cocokkanPassword(self::SANDI));
        $this->assertFalse($akun->cocokkanPassword('salah-sandi'));
    }

    public function test_provider_autentikasi_memakai_kolom_password_hash(): void
    {
        AkunPengguna::factory()->create(['email' => 'uji@contoh.test']);

        $this->assertTrue(Auth::validate(['email' => 'uji@contoh.test', 'password' => self::SANDI]));
        $this->assertFalse(Auth::validate(['email' => 'uji@contoh.test', 'password' => 'salah']));
    }

    // ---------------------------------------------------------------- profil dan detail

    public function test_profil_tidak_memuat_email_dan_hash(): void
    {
        $akun = AkunPengguna::factory()->create();
        $profil = $akun->getProfil();

        $this->assertSame(['id_akun', 'username', 'nama_tampilan', 'peran'], array_keys($profil));
        $this->assertSame($akun->id_akun, $profil['id_akun']);
        $this->assertArrayNotHasKey('email', $profil);
    }

    public function test_detail_memuat_email_tanpa_hash(): void
    {
        $akun = AkunPengguna::factory()->create(['email' => 'budi@contoh.test']);
        $detail = $akun->getDetailAkun();

        $this->assertSame('budi@contoh.test', $detail['email']);
        $this->assertArrayNotHasKey('password_hash', $detail);
        $this->assertArrayNotHasKey('password_hash', $akun->toArray());
    }

    public function test_nama_tampilan_dan_inisial(): void
    {
        $akun = AkunPengguna::factory()->create(['nama_tampilan' => 'sari']);

        $this->assertSame('sari', $akun->ambilNamaTampilan());
        $this->assertSame('S', $akun->inisial());
    }

    // ---------------------------------------------------------------- hak akses

    #[DataProvider('hakProvider')]
    public function test_verifikasi_hak_akses(Peran $peran, string $aksi, bool $harapan): void
    {
        $akun = AkunPengguna::factory()->create(['peran' => $peran->value]);

        $this->assertSame($harapan, $akun->verifikasiHakAkses($aksi));
    }

    public static function hakProvider(): array
    {
        return [
            'pelajar kirim feedback'    => [Peran::Pelajar, 'KIRIM_FEEDBACK', true],
            'pelajar kelola materi'     => [Peran::Pelajar, 'KELOLA_MATERI', false],
            'pelajar buat kelas'        => [Peran::Pelajar, 'BUAT_KELAS', false],
            'pengajar camelCase'        => [Peran::Pengajar, 'terbitkanTugas', true],
            'pengajar snake_case'       => [Peran::Pengajar, 'pantau_progres', true],
            'pengajar latihan'          => [Peran::Pengajar, 'MENGERJAKAN_LATIHAN', false],
            'tim materi camelCase'      => [Peran::TimMateri, 'kelolaMateri', true],
            'tim materi kirim feedback' => [Peran::TimMateri, 'KIRIM_FEEDBACK', false],
        ];
    }

    public function test_hak_akses_false_untuk_akun_tanpa_id(): void
    {
        $akun = new AkunPengguna(['peran' => Peran::Pelajar->value]);

        $this->assertFalse($akun->verifikasiHakAkses('KIRIM_FEEDBACK'));
    }

    public function test_hak_akses_false_untuk_peran_asing_dan_peran_huruf_besar_tetap_dikenali(): void
    {
        $asing = new AkunPengguna(['peran' => 'tamu']);
        $asing->id_akun = 1;
        $this->assertFalse($asing->verifikasiHakAkses('KIRIM_FEEDBACK'));

        $besar = new AkunPengguna(['peran' => 'PELAJAR']);
        $besar->id_akun = 2;
        $this->assertTrue($besar->verifikasiHakAkses('KIRIM_FEEDBACK'));
    }

    // ---------------------------------------------------------------- memilikiPeran

    public function test_memiliki_peran_menerima_enum_dan_teks(): void
    {
        $akun = AkunPengguna::factory()->pengajar()->create();

        $this->assertTrue($akun->memilikiPeran(Peran::Pengajar));
        $this->assertTrue($akun->memilikiPeran('PENGAJAR'));
        $this->assertTrue($akun->memilikiPeran(Peran::Pelajar, Peran::Pengajar));
        $this->assertFalse($akun->memilikiPeran(Peran::TimMateri, 'pelajar'));
    }
}