<?php

namespace App\Http\Controllers;

use App\Models\AkunPengguna;
use App\Support\HasilOperasi;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\View\View;
use App\Enums\Peran;
use App\Http\Requests\DaftarRequest;
use App\Support\DataPendaftaran;
use Illuminate\Support\Facades\DB;

/** C15 OtentikasiController. */
class OtentikasiController extends Controller
{
    private const BATAS_PERCOBAAN = 5;
    private const JENDELA_DETIK = 60;
    private const PESAN_SALAH = 'Email atau kata sandi salah';

    /** Hash palsu untuk email yang tidak terdaftar. */
    private static ?string $hashPalsu = null;

    public function tampilMasuk(): View
    {
        return view('auth.masuk');
    }

    public function prosesMasuk(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'email'    => ['required', 'string', 'max:255'],
            'password' => ['required', 'string'],
        ]);

        $email = strtolower(trim($data['email']));
        $kunci = 'login:'.$email.'|'.$request->ip();

        if (RateLimiter::tooManyAttempts($kunci, self::BATAS_PERCOBAAN)) {
            return $this->kembaliDenganGalat(
                $email,
                RateLimiter::availableIn($kunci),
            );
        }

        $hasil = $this->validasiLogin($email, $data['password']);

        if (! $hasil->berhasil) {
            RateLimiter::hit($kunci, self::JENDELA_DETIK);
            Log::warning('Login gagal', ['ip' => $request->ip()]); // tanpa email

            return $this->kembaliDenganGalat($email, null);
        }

        RateLimiter::clear($kunci);
        Auth::login($hasil->data['akun']);
        $request->session()->regenerate(); // mencegah session fixation

        return redirect()->intended($this->tujuanSetelahMasuk());
    }

    public function keluar(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('masuk')->with('sukses', 'Anda sudah keluar.');
    }

    public function validasiLogin(string $email, string $password): HasilOperasi
    {
        $akun = AkunPengguna::cariBerdasarkanEmail($email);

        if ($akun === null) {
            Hash::check($password, self::hashPalsu());

            return HasilOperasi::gagal('KREDENSIAL_SALAH', self::PESAN_SALAH);
        }

        if (! $akun->cocokkanPassword($password)) {
            return HasilOperasi::gagal('KREDENSIAL_SALAH', self::PESAN_SALAH);
        }

        return HasilOperasi::sukses(['akun' => $akun]);
    }

    private function kembaliDenganGalat(string $email, ?int $tungguDetik): RedirectResponse
    {
        $galat = $tungguDetik !== null
            ? self::PESAN_SALAH.". Coba lagi dalam {$tungguDetik} detik."
            : self::PESAN_SALAH;

        return back()
            ->withInput(['email' => $email])
            ->with('galat', $galat)
            ->with('tunggu', $tungguDetik);
    }

    private function tujuanSetelahMasuk(): string
    {
        return Route::has('beranda') ? route('beranda') : url('/');
    }

    private static function hashPalsu(): string
    {
        return self::$hashPalsu ??= Hash::make(Str::random(40));
    }

    // ----------------------------------------------------------daftar

    public function tampilDaftar(): View
    {
        return view('auth.daftar');
    }

    /** Algo-005 (sisi server) -> Algo-009 (DaftarRequest) -> Algo-011 (simpanAkun). */
    public function prosesDaftar(DaftarRequest $request): RedirectResponse
    {
        $hasil = $this->simpanAkun($request->validated());

    if (! $hasil->berhasil) {
        $keterangan = [
            'EMAIL_TERDAFTAR'   => 'Gunakan email lain atau masuk dengan akun yang ada.',
            'USERNAME_TERPAKAI' => 'Gunakan username lain.',
            'AKUN_SUDAH_ADA'    => 'Periksa kembali email dan username, lalu coba lagi.',
        ][$hasil->kode] ?? 'Silakan coba lagi.';

        return back()
            ->withInput($request->except('password')) // password cleared (Algo-005)
            ->withErrors(['akun' => $keterangan, 'akun_judul' => $hasil->pesan]);
    }

        return redirect()->route('masuk')->with('sukses', 'Akun berhasil dibuat. Silakan masuk.');
    }

    /**
     * Algo-011. Data sudah divalidasi oleh DaftarRequest (Algo-009).
     * Baris motivasi_belajar dibuat untuk Pelajar dalam transaksi yang sama.
     *
     * param  array{email:string, username:string, password:string, peran:string, nama_tampilan?:?string}  $data
     */
    public function simpanAkun(array $data): HasilOperasi
    {
        $dataSimpan = new DataPendaftaran(
            email: $data['email'],
            username: $data['username'],
            passwordHash: $this->hashPassword($data['password']),
            peran: Peran::from($data['peran']),
            namaTampilan: $data['nama_tampilan'] ?? null,
        );

        try {
            $hasil = DB::transaction(function () use ($dataSimpan) {
                $akun = new AkunPengguna();
                $hasil = $akun->buatAkunBaru($dataSimpan); // Algo-001

                if ($hasil->berhasil && $dataSimpan->peran === Peran::Pelajar) {
                    $akun->motivasi()->create([]); // baris awal motivasi_belajar
                }

                return $hasil;
            });
        } catch (\Throwable) {
            Log::error('Pendaftaran gagal', ['username' => $data['username']]); // tanpa sandi dan email

            return HasilOperasi::gagal('SIMPAN_GAGAL', 'Akun gagal dibuat, coba lagi');
        }

        if ($hasil->berhasil) {
            Log::info('Akun baru dibuat', ['id_akun' => $hasil->data['id_akun']]);
        }

        return $hasil;
    }

    /** Algo-010. Hash bcrypt bersalt acak; sandi asli tidak pernah disimpan atau dicatat. */
    private function hashPassword(string $password): string
    {
        return Hash::make($password, ['rounds' => 12]);
    }
}