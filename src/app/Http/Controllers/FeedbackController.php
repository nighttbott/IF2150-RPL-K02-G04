<?php

namespace App\Http\Controllers;

use App\Enums\KategoriFeedback;
use App\Models\AkunPengguna;
use App\Models\DataFeedback;
use App\Support\HasilOperasi;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

/** C18 FeedbackController: validasi, simpan, dan konfirmasi feedback. */
class FeedbackController extends Controller
{
    private const PESAN_BERHASIL = 'Feedback terkirim. Matur nuwun!';
    private const PESAN_GAGAL = 'Feedback gagal terkirim. Periksa koneksi lalu coba lagi. Isi pesanmu tidak hilang.';

    /** form feedback. */
    public function index(Request $request): View
    {
        $this->pastikanHakAkses($request->user());

        return view('feedback.index', ['kategori' => KategoriFeedback::cases()]);
    }

    /** Alur Algo-008: validasi, simpan, lalu konfirmasi. */
    public function kirim(Request $request): RedirectResponse
    {
        $akun = $request->user();
        $data = $request->only(['kategori', 'pesan']);

        $galat = $this->validasiFeedback($akun, $data);
        if ($galat !== []) {
            return back()->withInput()->withErrors($galat); // galat di bawah isian
        }

        $konfirmasi = $this->kirimKonfirmasi($this->simpanFeedback($akun, $data));

        if (! $konfirmasi['berhasil']) {
            return back()->withInput()->with('galat', $konfirmasi['pesan']); // isian dipertahankan
        }

        return redirect()->route('feedback.index')->with('sukses', $konfirmasi['pesan']); // form kosong
    }

    /** Mengembalikan peta galat per isian; kosong berarti valid. Tanpa hak KIRIM_FEEDBACK permintaan ditolak 403. @return array<string, string> */
    public function validasiFeedback(?AkunPengguna $akun, array $data): array
    {
        $this->pastikanHakAkses($akun);

        $galat = [];

        if (KategoriFeedback::tryFrom((string) ($data['kategori'] ?? '')) === null) {
            $galat['kategori'] = 'Pilih kategori feedback';
        }

        $panjang = mb_strlen(trim((string) ($data['pesan'] ?? '')));
        if ($panjang < 10 || $panjang > 1000) {
            $galat['pesan'] = 'Feedback 10–1.000 karakter';
        }

        return $galat;
    }

    /** Validasi ulang di sisi server; pengirim dari sesi, bukan dari browser. */
    public function simpanFeedback(AkunPengguna $akun, array $data): HasilOperasi
    {
        $galat = $this->validasiFeedback($akun, $data);
        if ($galat !== []) {
            return HasilOperasi::gagal('DATA_TIDAK_VALID', $galat);
        }

        $profil = $akun->getProfil();

        $hasil = (new DataFeedback())->simpan([
            'id_akun'  => $profil['id_akun'],
            'kategori' => $data['kategori'],
            'pesan'    => trim($data['pesan']),
        ]);

        if (! $hasil->berhasil) {
            Log::error('Feedback gagal disimpan', ['id_akun' => $profil['id_akun']]); // KF17
        }

        return $hasil;
    }

    /** return array{berhasil: bool, pesan: string} */
    public function kirimKonfirmasi(HasilOperasi $hasil): array
    {
        return $hasil->berhasil
            ? ['berhasil' => true, 'pesan' => self::PESAN_BERHASIL]
            : ['berhasil' => false, 'pesan' => self::PESAN_GAGAL];
    }

    private function pastikanHakAkses(?AkunPengguna $akun): void
    {
        if ($akun === null || ! $akun->verifikasiHakAkses('KIRIM_FEEDBACK')) {
            abort(403, 'Fitur feedback khusus Pelajar dan Pengajar');
        }
    }
}