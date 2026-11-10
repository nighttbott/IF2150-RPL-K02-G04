<?php

namespace App\Http\Controllers;

use App\Enums\ModeLatihan;
use App\Enums\StatusPublikasi;
use App\Http\Requests\Latihan\KirimJawabanRequest;
use App\Http\Requests\Latihan\SelesaikanLatihanRequest;
use App\Latihan\AkurasiGoresan;
use App\Latihan\Data\HasilLatihan;
use App\Latihan\Data\HasilPenilaian;
use App\Latihan\Data\JawabanLatihan;
use App\Latihan\Data\RingkasanMotivasi;
use App\Latihan\Data\SoalLatihan;
use App\Latihan\PemeriksaBadge;
use App\Models\MateriAksara;
use App\Models\MotivasiBelajar;
use App\Models\RiwayatLatihan;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;
use LogicException;
use RuntimeException;
use Throwable;

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
        $request->validate([
            'tugas' => ['nullable', 'integer', 'required_with:komponen'],
            'komponen' => ['nullable', 'integer', 'required_with:tugas'],
        ]);
        $konteks = $request->filled('tugas')
            ? ['idTugas' => $request->integer('tugas'), 'idKomponen' => $request->integer('komponen')]
            : null;
        $aktif = $request->session()->get('latihan.soalAktif');
        $soal = $aktif ? SoalLatihan::fromArray($aktif['soal']) : null;
        $soal = $soal && $soal->idMateri === $materi && $soal->mode === $mode && ($aktif['konteksTugas'] ?? null) === $konteks
            ? $soal
            : $this->siapkanSoal($request, $materi, $mode, $konteks);
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
        $hasil = new HasilLatihan(
            $request->user()->getKey(), $soal->idMateri, $soal->idSoal, $soal->targetSoal, $soal->mode, $skor, $status,
            $aktif['konteksTugas']['idTugas'] ?? null, $aktif['konteksTugas']['idKomponen'] ?? null,
        );
        if ($status === 'perlu_diulang') {
            $this->simpanRiwayat($hasil);
        }
        $request->session()->put('latihan.hasilTerakhir', $hasil->toArray());

        $akurasi = 'Akurasi '.str_replace('.', ',', (string) $skor).'%.';

        return response()->json(new HasilPenilaian(
            $skor,
            $status,
            $status === 'berhasil' ? $akurasi : "$akurasi Tulis lebih dekat ke garis bantu.",
        ));
    }

    /**
     * Algo-026, DPPL 5.16.6. Hasil diambil dari session, bukan dari body request.
     */
    public function simpanHasil(SelesaikanLatihanRequest $request): JsonResponse
    {
        $hasil = $request->session()->get('latihan.hasilTerakhir');
        $hasil = $hasil ? HasilLatihan::fromArray($hasil) : null;
        if (! $hasil || $hasil->idSoal !== $request->input('idSoal') || $hasil->status !== 'berhasil') {
            return $this->gagal('BELUM_BERHASIL', 'Latihan belum berhasil diselesaikan', 422);
        }

        try {
            $ringkasan = DB::transaction(function () use ($hasil) {
                // Dicek sebelum hasil baru disimpan; sesudahnya baris baru ini sendiri akan terhitung.
                $pernah = RiwayatLatihan::cekPernahSelesai($hasil->idPelajar, $hasil->targetSoal, $hasil->mode);
                $idRiwayat = $this->simpanRiwayat($hasil);
                $m = MotivasiBelajar::milikPelajar($hasil->idPelajar, kunci: true);
                $m->perbaruiStreak(today());
                if (! $pernah) {
                    $m->tambahPoin($idRiwayat, config('latihan.poin_per_soal'));
                }
                $badgeBaru = $this->periksaBadge($m->streak, $m->poin, $m->badge());
                foreach ($badgeBaru as $kode) {
                    $m->berikanBadge($kode);
                }

                return new RingkasanMotivasi($m->streak, $m->poin, ! $pernah, $badgeBaru);
            });
        } catch (Throwable $e) {
            Log::error('Gagal menyimpan hasil latihan', ['id_pelajar' => $hasil->idPelajar, 'galat' => $e->getMessage()]);

            return $this->gagal('GAGAL_SIMPAN', 'Hasil belum tersimpan. Coba lagi.', 500);
        }

        $sesi = $request->session()->get('latihan.sesi');
        $sesi['selesai'] = array_values(array_unique([...$sesi['selesai'], $hasil->targetSoal]));
        $request->session()->put('latihan.sesi', $sesi);
        $request->session()->forget(['latihan.hasilTerakhir', 'latihan.soalAktif']);
        $request->session()->put('latihan.ringkasanTerakhir', [
            'ringkasan' => $ringkasan->toArray(),
            'skor' => $hasil->skor,
            'idMateri' => $hasil->idMateri,
            'namaMateri' => $this->namaMateri($hasil->idMateri),
            'mode' => $hasil->mode->value,
            'idTugas' => $hasil->idTugas,
        ]);

        return response()->json(['ok' => true, 'redirect' => route('latihan.hasil')]);
    }

    /** I-10 Hasil Latihan. */
    public function hasil(Request $request): View|RedirectResponse
    {
        $data = $request->session()->get('latihan.ringkasanTerakhir');

        if (! $data) {
            return redirect()->route('beranda');
        }

        return view('latihan.hasil', [
            ...$data,
            'ringkasan' => RingkasanMotivasi::fromArray($data['ringkasan']),
            'mode' => ModeLatihan::from($data['mode']),
        ]);
    }

    /** I-10-06: sesi diulang dari awal dengan soal baru pada mode yang sama. */
    public function ulangi(Request $request, int $materi, ModeLatihan $mode): RedirectResponse
    {
        $request->session()->forget(['latihan.soalAktif', 'latihan.sesi']);

        return redirect()->route('latihan.soal', [$materi, $mode]);
    }

    /** I-10-07: soal berikutnya, atau kembali bila semua butir sesi sudah selesai. */
    public function lanjut(Request $request, int $materi, ModeLatihan $mode): RedirectResponse
    {
        $request->session()->forget('latihan.soalAktif');
        $sesi = $request->session()->get('latihan.sesi');
        if ($sesi && count($sesi['selesai']) >= $sesi['total']) {
            $request->session()->forget('latihan.sesi');

            return redirect()->route('materi.show', $materi);
        }

        return redirect()->route('latihan.soal', [$materi, $mode]);
    }

    /**
     * Algo-021, DPPL 5.16.1. Memilih acak dari butir yang belum diselesaikan di sesi ini.
     */
    private function siapkanSoal(Request $request, int $idMateri, ModeLatihan $mode, ?array $konteksTugas = null): SoalLatihan
    {
        if (! view()->exists("latihan.{$mode->value}")) {
            abort(422, 'Mode latihan tidak dikenal');
        }
        if ($konteksTugas) {
            $this->validasiKonteksTugas($request->user()->getKey(), $konteksTugas, $idMateri, $mode);
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
        $request->session()->put('latihan.soalAktif', ['soal' => $soal->toArray(), 'kunci' => null, 'konteksTugas' => $konteksTugas]);
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

    /**
     * Pelajar harus anggota aktif kelas pemilik tugas terbit, dan komponen harus milik tugas itu
     * dengan materi dan mode yang sama. Tenggat yang lewat tidak menolak (SKPL 2.4 butir 7).
     * Sementara memakai query langsung, diganti validasi milik Abduh (TugasKelas, KeanggotaanKelas).
     */
    private function validasiKonteksTugas(int $idPelajar, array $konteks, int $idMateri, ModeLatihan $mode): void
    {
        $sah = DB::table('komponen_tugas as k')
            ->join('tugas_kelas as t', 't.id_tugas', '=', 'k.id_tugas')
            ->join('keanggotaan_kelas as a', 'a.id_kelas', '=', 't.id_kelas')
            ->where('k.id_tugas', $konteks['idTugas'])
            ->where('k.id_komponen', $konteks['idKomponen'])
            ->where('k.id_materi', $idMateri)
            ->where('k.mode', $mode->value)
            ->where('t.status', 'terbit')
            ->where('a.id_pelajar', $idPelajar)
            ->where('a.status', 'aktif')
            ->exists();
        abort_unless($sah, 403, 'Kamu tidak memiliki akses ke tugas ini.');
    }

    /** RiwayatLatihan.simpanHasil (Q-008). Kegagalan dilempar agar transaksi pemanggil dibatalkan. */
    private function simpanRiwayat(HasilLatihan $hasil): int
    {
        $catat = RiwayatLatihan::simpanHasil([
            'id_pelajar' => $hasil->idPelajar,
            'id_materi' => $hasil->idMateri,
            'target_soal' => $hasil->targetSoal,
            'mode' => $hasil->mode,
            'skor' => $hasil->skor,
            'status' => $hasil->status,
            // Hanya hasil berhasil yang menjadi bukti penyelesaian komponen tugas (Q-015).
            'id_tugas' => $hasil->status === 'berhasil' ? $hasil->idTugas : null,
            'id_komponen' => $hasil->status === 'berhasil' ? $hasil->idKomponen : null,
        ]);
        throw_unless($catat->berhasil, RuntimeException::class, $catat->pesan);

        return $catat->ambil('idRiwayat');
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
