<?php

namespace App\Http\Controllers;

use App\Enums\ModeLatihan;
use App\Enums\StatusKeanggotaan;
use App\Enums\StatusKelas;
use App\Enums\StatusPublikasi;
use App\Enums\StatusTugas;
use App\Models\MateriAksara;
use App\Models\MotivasiBelajar;
use App\Services\Latihan\PenilaiJawaban;
use App\Services\Latihan\PenyusunSoal;
use App\Services\Latihan\SelesaikanLatihan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * Bagian dari C16 LatihanController untuk mode non-kanvas (Marvel): bunyi (I-08), merangkai (I-09),
 * dan transliterasi. Mode menulis (kanvas) ada di berkas milik Naufal; catat pemecahan C16 di B.1.
 *
 * Alur: mulai() = siapkanSoal (Algo-021): menyusun soal dan menyimpan kuncinya di sesi server.
 * kirim() = nilaiJawaban (Algo-022; cocokkanBunyi Algo-024, validasiRangkaian Algo-025) dan
 * simpanHasil (Algo-026) lewat SelesaikanLatihan, lalu mengalihkan ke hasil() (I-10).
 * Sesi soal hanya bisa dipakai sekali sehingga kirim ulang tidak menggandakan poin.
 */
class LatihanNonKanvasController extends Controller
{
    private const KUNCI_SESI = 'latihan_non_kanvas';

    private const MAKS_SESI_TERSIMPAN = 5;

    public function __construct(
        private readonly PenyusunSoal $penyusun,
        private readonly SelesaikanLatihan $penyelesai,
    ) {}

    /** I-08 Bunyi, I-09 Merangkai, dan halaman transliterasi. Parameter opsional: ?tugas=ID&komponen=ID. */
    public function mulai(Request $request, string $mode, int $idMateri): View
    {
        $modeLatihan = $this->modeNonKanvas($mode);
        $materi = MateriAksara::query()->findOrFail($idMateri);
        abort_unless($materi->status_publikasi === StatusPublikasi::Terbit, 404);

        $idTugas = $request->integer('tugas') ?: null;
        $idKomponen = $request->integer('komponen') ?: null;
        if ($idTugas !== null || $idKomponen !== null) {
            $this->periksaKomponenTugas((int) $request->user()->id_akun, $idTugas, $idKomponen, $idMateri, $modeLatihan);
        }

        $soal = $this->penyusun->susun($materi, $modeLatihan, (int) config('ngaksara.latihan.jumlah_soal', 5));
        if ($soal === []) {
            return view('latihan.kosong', ['materi' => $materi, 'mode' => $modeLatihan]);
        }

        $token = Str::random(32);
        $semua = session(self::KUNCI_SESI, []);
        $semua[$token] = [
            'mode' => $modeLatihan->value,
            'id_materi' => $idMateri,
            'id_pelajar' => (int) $request->user()->id_akun,
            'id_tugas' => $idTugas,
            'id_komponen' => $idKomponen,
            'dibuat' => now()->timestamp,
            'soal' => $soal,
        ];
        session([self::KUNCI_SESI => array_slice($semua, -self::MAKS_SESI_TERSIMPAN, null, true)]);

        return view('latihan.'.$modeLatihan->value, [
            'materi' => $materi,
            'token' => $token,
            'soal' => PenyusunSoal::publik($soal),
            // I-08: audio pengucapan materi (Algo-021 cabang BUNYI); null bila belum diunggah Tim Materi.
            'audio' => $modeLatihan === ModeLatihan::Bunyi && $materi->konten?->ambilAudio()->berhasil
                ? route('materi.audio', $idMateri)
                : null,
        ]);
    }

    /** Algo-022 nilaiJawaban + Algo-026 simpanHasil. */
    public function kirim(Request $request, string $mode): RedirectResponse
    {
        $modeLatihan = $this->modeNonKanvas($mode);
        $request->validate([
            'token' => ['required', 'string'],
            'jawaban' => ['nullable', 'array'],
            'jawaban.*' => ['nullable', 'string', 'max:200'],
            'urutan' => ['nullable', 'array'],
            'urutan.*' => ['nullable', 'array'],
            'urutan.*.*' => ['nullable', 'string', 'max:20'],
        ]);

        $semua = session(self::KUNCI_SESI, []);
        $sesi = $semua[$request->input('token')] ?? null;
        unset($semua[$request->input('token')]);
        session([self::KUNCI_SESI => $semua]); // sekali pakai

        $masaBerlaku = (int) config('ngaksara.latihan.masa_berlaku_menit', 60) * 60;
        if ($sesi === null
            || $sesi['mode'] !== $modeLatihan->value
            || $sesi['id_pelajar'] !== (int) $request->user()->id_akun
            || now()->timestamp - $sesi['dibuat'] > $masaBerlaku) {
            return redirect()->route('materi.index')->with('galat', 'Sesi latihan sudah berakhir atau sudah dikirim. Mulai latihan lagi.');
        }

        $ambang = (float) config('ngaksara.latihan.ambang_berhasil', 70);
        $rincian = [];
        $total = 0.0;
        foreach ($sesi['soal'] as $soal) {
            $nilai = $this->nilai($modeLatihan, $soal, $request);
            $total += $nilai['penilaian']->skor;
            $rincian[] = $nilai;
        }
        $rataRata = $total / count($rincian);

        // Soal sebuah sesi tugas dihitung sebagai bukti komponen hanya bila sesi lulus (rata-rata >= ambang).
        $untukTugas = $sesi['id_tugas'] !== null && $sesi['id_komponen'] !== null && $rataRata >= $ambang;

        $daftarHasil = array_map(fn ($r) => [
            'id_materi' => $sesi['id_materi'],
            'target_soal' => mb_substr($r['target'], 0, 50),
            'mode' => $modeLatihan->value,
            'skor' => $r['penilaian']->skor,
            'status' => $r['penilaian']->berhasil($ambang) ? 'berhasil' : 'perlu_diulang',
            'id_tugas' => $untukTugas ? $sesi['id_tugas'] : null,
            'id_komponen' => $untukTugas ? $sesi['id_komponen'] : null,
        ], $rincian);

        $simpan = $this->penyelesai->selesaikan((int) $request->user()->id_akun, $daftarHasil);
        if (! $simpan->berhasil) {
            return redirect()->route('materi.show', $sesi['id_materi'])->with('galat', $simpan->pesan);
        }

        $badge = array_map(fn ($kode) => MotivasiBelajar::BADGE[$kode] ?? $kode, $simpan->ambil('badgeBaru', []));

        return redirect()->route('latihan.soal.hasil')->with('hasil_latihan', [
            'mode' => $modeLatihan->value,
            'id_materi' => $sesi['id_materi'],
            'rincian' => array_map(fn ($r) => [
                'target' => $r['target'],
                'bunyi' => $r['bunyi'] ?? null,
                'jawaban' => $r['jawaban'],
                'kunci' => $r['kunci'],
                'benar' => $r['penilaian']->benar,
                'skor' => $r['penilaian']->skor,
                'posisi_salah' => $r['penilaian']->posisiSalah,
            ], $rincian),
            'rata_rata' => round($rataRata, 2),
            'lulus' => $rataRata >= $ambang,
            'poin_didapat' => $simpan->ambil('poinDidapat', 0),
            'total_poin' => $simpan->ambil('totalPoin', 0),
            'streak' => $simpan->ambil('streak', 0),
            'badge_baru' => $badge,
            'dari_tugas' => $sesi['id_tugas'] !== null,
        ]);
    }

    /** I-10 Hasil Latihan. Data berasal dari flash sesi; tanpa data, kembali ke daftar materi. */
    public function hasil(): View|RedirectResponse
    {
        $hasil = session('hasil_latihan');
        if ($hasil === null) {
            return redirect()->route('materi.index');
        }

        return view('latihan.hasil', ['hasil' => $hasil, 'materi' => MateriAksara::query()->find($hasil['id_materi'])]);
    }

    /**
     * @return array{target: string, bunyi?: ?string, jawaban: string, kunci: string, penilaian: \App\Services\Latihan\HasilPenilaian}
     */
    private function nilai(ModeLatihan $mode, array $soal, Request $request): array
    {
        $id = $soal['id'];

        if ($mode === ModeLatihan::Bunyi) {
            $pilihan = $request->input("jawaban.$id");
            $teks = collect($soal['opsi'])->firstWhere('id', $pilihan)['teks'] ?? '';
            $teksKunci = collect($soal['opsi'])->firstWhere('id', $soal['kunci'])['teks'] ?? '';

            return ['target' => $soal['target'], 'bunyi' => $soal['bunyi'] ?? null, 'jawaban' => $teks, 'kunci' => $teksKunci, 'penilaian' => PenilaiJawaban::bunyi($pilihan, $soal['kunci'])];
        }

        if ($mode === ModeLatihan::Merangkai) {
            $peta = collect($soal['ubin'])->pluck('teks', 'id')->all();
            $dipakai = [];
            $urutan = [];
            foreach ((array) $request->input("urutan.$id", []) as $idUbin) {
                // Setiap ubin hanya boleh dipakai sekali; ubin tidak dikenal atau berulang dihitung salah.
                $urutan[] = (isset($peta[$idUbin]) && ! isset($dipakai[$idUbin])) ? $peta[$idUbin] : '';
                $dipakai[$idUbin] = true;
            }

            return ['target' => $soal['target'], 'jawaban' => implode('', $urutan), 'kunci' => implode('', $soal['kunci']), 'penilaian' => PenilaiJawaban::merangkai($urutan, $soal['kunci'])];
        }

        $jawaban = (string) $request->input("jawaban.$id", '');

        return ['target' => $soal['target'], 'jawaban' => $jawaban, 'kunci' => $soal['kunci'], 'penilaian' => PenilaiJawaban::transliterasi($jawaban, $soal['kunci'])];
    }

    private function modeNonKanvas(string $mode): ModeLatihan
    {
        $modeLatihan = ModeLatihan::tryFrom($mode);
        abort_if($modeLatihan === null || $modeLatihan === ModeLatihan::Menulis, 404);

        return $modeLatihan;
    }

    /**
     * Komponen tugas harus: milik tugas yang terbit, berjenis latihan dengan mode dan materi yang sama,
     * pada kelas aktif yang Pelajar ini masih menjadi anggota aktifnya.
     */
    private function periksaKomponenTugas(int $idPelajar, ?int $idTugas, ?int $idKomponen, int $idMateri, ModeLatihan $mode): void
    {
        abort_if($idTugas === null || $idKomponen === null, 422, 'Tugas dan komponen harus diisi bersama.');

        $sah = DB::table('komponen_tugas as c')
            ->join('tugas_kelas as t', 't.id_tugas', '=', 'c.id_tugas')
            ->join('kelas_belajar as k', 'k.id_kelas', '=', 't.id_kelas')
            ->join('keanggotaan_kelas as a', function ($join) use ($idPelajar) {
                $join->on('a.id_kelas', '=', 'k.id_kelas')->where('a.id_pelajar', $idPelajar);
            })
            ->where('c.id_komponen', $idKomponen)
            ->where('c.id_tugas', $idTugas)
            ->where('c.jenis', 'latihan')
            ->where('c.mode', $mode->value)
            ->where('c.id_materi', $idMateri)
            ->where('t.status', StatusTugas::Terbit->value)
            ->where('k.status', StatusKelas::Aktif->value)
            ->where('a.status', StatusKeanggotaan::Aktif->value)
            ->exists();

        abort_unless($sah, 403, 'Komponen tugas tidak valid untuk Anda.');
    }
}
