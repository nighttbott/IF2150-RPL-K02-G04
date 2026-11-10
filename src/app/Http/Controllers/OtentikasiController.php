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

/** C15 OtentikasiController. */
class OtentikasiController extends Controller
{
    private const BATAS_PERCOBAAN = 5;
    private const JENDELA_DETIK = 60;
    private const PESAN_SALAH = 'Email atau kata sandi salah';

    /** Hash palsu untuk email yang tidak terdaftar, supaya waktu respons sama (Algo-012). */
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

    /** Algo-012. Tidak pernah membedakan email salah dari sandi salah. */
    public function validasiLogin(string $email, string $password): HasilOperasi
    {
        $akun = AkunPengguna::cariBerdasarkanEmail($email);

        if ($akun === null) {
            Hash::check($password, self::hashPalsu()); // samakan waktu respons

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
}