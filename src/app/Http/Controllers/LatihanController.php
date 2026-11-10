<?php

namespace App\Http\Controllers;

use App\Enums\ModeLatihan;
use App\Enums\StatusPublikasi;
use App\Http\Requests\Latihan\KirimJawabanRequest;
use App\Latihan\AkurasiGoresan;
use App\Latihan\Data\HasilLatihan;
use App\Latihan\Data\HasilPenilaian;
use App\Latihan\Data\JawabanLatihan;
use App\Latihan\Data\SoalLatihan;
use App\Latihan\PemeriksaBadge;
use App\Models\MateriAksara;
use App\Models\MotivasiBelajar;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use LogicException;

/**
 * C16 LatihanController (DPPL 5.16). Atribut `konteks` diwujudkan session Laravel.
 * Hak akses Pelajar (KF02) dijaga middleware peran:pelajar di routes/latihan.php.
 */
class LatihanController extends Controller
{
    /** I-06 Pilih Mode Latihan. */
    public function pilihMode(Request $request, int $materi): View
    {

        return view('latihan.pilih-mode', [
            'idMateri' => $materi,
            'namaMateri' => $this->namaMateri($materi),
            'motivasi' => MotivasiBelajar::ambilMotivasi($request->user()->getKey()),
        ]);
    }

    /** I-07: memakai ulang soal aktif bila materi dan mode sama, supaya refresh tidak mengganti soal. */
    public function soal(Request $request, int $materi, ModeLatihan $mode): View
    {
        $aktif = $request->session()->get('latihan.soalAktif');
        $soal = $aktif ? SoalLatihan::fromArray($aktif['soal']) : null;
        $soal = $soal && $soal->idMateri === $materi && $soal->mode === $mode
            ? $soal
            : $this->siapkanSoal($request, $materi, $mode);
        $sesi = $request->session()->get('latihan.sesi');

        return view("latihan.{$mode->value}", [
            'soal' => $soal,
            'selesai' => count($sesi['selesai']),
            'total' => $sesi['total'],
            'streak' => MotivasiBelajar::ambilMotivasi($request->user()->getKey())['streak'],
        ]);
    }

    /** Algo-022, DPPL 5.16.2. */
    public function nilaiJawaban(KirimJawabanRequest $request): JsonResponse
    {
        $aktif = $request->session()->get('latihan.soalAktif');
        if (! $aktif || $aktif['soal']['idSoal'] !== $request->input('idSoal')) {
            return $this->gagal('SOAL_TIDAK_DITEMUKAN', 'Soal tidak ditemukan. Muat ulang latihan.', 409);
        }
        $soal = SoalLatihan::fromArray($aktif['soal']);
        $jawaban = new JawabanLatihan($soal->idSoal, $soal->mode, goresan: $request->input('goresan'));

        $skor = match ($soal->mode) {
            ModeLatihan::Menulis => $this->hitungAkurasiGoresan($jawaban->goresan, $soal->templat ?? []),
            default => throw new LogicException('Mode ini belum tersedia.'),
        };
        $status = $skor >= config('latihan.ambang_akurasi') ? 'berhasil' : 'perlu_diulang';
        $hasil = new HasilLatihan($request->user()->getKey(), $soal->idMateri, $soal->idSoal, $soal->targetSoal, $soal->mode, $skor, $status);
        $request->session()->put('latihan.hasilTerakhir', $hasil->toArray());

        $akurasi = 'Akurasi '.str_replace('.', ',', (string) $skor).'%.';

        return response()->json(new HasilPenilaian(
            $skor,
            $status,
            $status === 'berhasil' ? $akurasi : "$akurasi Tulis lebih dekat ke garis bantu.",
        ));
    }

    /**
     * Algo-021, DPPL 5.16.1. Memilih acak dari butir yang belum diselesaikan di sesi ini.
     */
    private function siapkanSoal(Request $request, int $idMateri, ModeLatihan $mode): SoalLatihan
    {
        if (! view()->exists("latihan.{$mode->value}")) {
            abort(422, 'Mode latihan tidak dikenal');
        }

        $butirSah = $this->materi($idMateri)->ambilTargetLatihan($mode->value)->butir;
        if ($mode === ModeLatihan::Menulis) {
            // Karakter tanpa templat (misalnya sandhangan) tidak bisa dinilai Algo-023.
            $butirSah = array_values(array_filter($butirSah, fn ($b) => ! empty($b['template'])));
        }
        if ($butirSah === []) {
            throw new HttpResponseException(redirect()->route('latihan.mode', $idMateri)
                ->with('galat', 'Materi ini belum memiliki soal untuk mode yang dipilih'));
        }

        $sesi = $request->session()->get('latihan.sesi');
        if (! $sesi || $sesi['idMateri'] !== $idMateri || $sesi['mode'] !== $mode->value) {
            $sesi = ['idMateri' => $idMateri, 'mode' => $mode->value, 'selesai' => [], 'total' => count($butirSah)];
        }
        $sisa = array_filter($butirSah, fn ($b) => ! in_array($b['karakter'], $sesi['selesai'], true));
        $butir = collect($sisa ?: $butirSah)->random();

        $soal = match ($mode) {
            ModeLatihan::Menulis => new SoalLatihan(
                (string) Str::uuid(), $idMateri, $mode, $butir['karakter'], $butir['bunyi'] ?? '',
                templat: $butir['template'],
            ),
            default => throw new LogicException('Mode ini belum tersedia.'),
        };

        $request->session()->put('latihan.sesi', $sesi);
        $request->session()->put('latihan.soalAktif', ['soal' => $soal->toArray(), 'kunci' => null]);
        $request->session()->forget('latihan.hasilTerakhir');

        return $soal;
    }

    /**
     * Algo-023, DPPL 5.16.3. Logika ada di AkurasiGoresan agar bisa diuji tanpa HTTP.
     */
    private function hitungAkurasiGoresan(array $goresan, array $templat): float
    {
        return (new AkurasiGoresan(config('latihan.jumlah_titik'), config('latihan.d_maks')))->hitung($goresan, $templat);
    }

    // Diisi Marvel: mode bunyi (Algo-024).
    private function cocokkanBunyi(string $pilihan, string $kunci): bool
    {
        throw new LogicException('Mode ini belum tersedia.');
    }

    // Diisi Marvel: mode merangkai (Algo-025).
    private function validasiRangkaian(array $urutan, array $kunci): array
    {
        throw new LogicException('Mode ini belum tersedia.');
    }

    /**
     * Algo-027, DPPL 5.16.7.
     *
     * @return list<string>
     */
    private function periksaBadge(int $streak, int $poin, array $dimiliki): array
    {
        return (new PemeriksaBadge)->periksa($streak, $poin, $dimiliki);
    }

    /** Hanya materi terbit yang bisa dilatih; draf dan arsip dianggap tidak ada. */
    private function materi(int $idMateri): MateriAksara
    {
        return MateriAksara::where('status_publikasi', StatusPublikasi::Terbit->value)->findOrFail($idMateri);
    }

    private function namaMateri(int $idMateri): string
    {
        return $this->materi($idMateri)->nama_materi;
    }

    private function gagal(string $kode, string $pesan, int $status): JsonResponse
    {
        return response()->json(['kode' => $kode, 'message' => $pesan], $status);
    }
}
