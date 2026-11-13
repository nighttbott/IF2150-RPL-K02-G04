<?php

namespace App\Http\Controllers;

use App\Enums\KategoriFeedback;
use App\Enums\StatusFeedback;
use App\Enums\StatusKeanggotaan;
use App\Enums\StatusKelas;
use App\Models\MateriAksara;
use App\Models\RiwayatLatihan;
use App\Support\Periode;
use App\Support\Progres\HasilOperasi;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

/** Bagian C20 KelasController untuk pemantauan (UC-08), pemilik modul: Natan. */
class PemantauanKelasController extends Controller
{
    /** Daftar kelas aktif milik Pengajar. Bila hanya satu kelas, langsung dibuka. */
    public function daftarKelas(Request $request): View|RedirectResponse
    {
        $kelas = DB::table('kelas_belajar as k')
            ->leftJoin('keanggotaan_kelas as a', function ($join) {
                $join->on('a.id_kelas', '=', 'k.id_kelas')->where('a.status', StatusKeanggotaan::Aktif->value);
            })
            ->where('k.pemilik_id', $request->user()->id_akun)
            ->where('k.status', StatusKelas::Aktif->value)
            ->groupBy('k.id_kelas', 'k.nama')
            ->orderBy('k.nama')
            ->get(['k.id_kelas', 'k.nama', DB::raw('COUNT(a.id_keanggotaan) AS jumlah_anggota')]);

        if ($kelas->count() === 1) {
            return redirect()->route('pemantauan.index', $kelas->first()->id_kelas);
        }

        return view('pemantauan.daftar-kelas', ['daftarKelas' => $kelas]);
    }

    /** Halaman I-17: ringkasan kelas, progres tiap anggota aktif, pola kesalahan, dan detail satu anggota bila ?pelajar=ID diisi (pilihAnggota). */
    public function tampilkan(Request $request, int $idKelas): View
    {
        $kelas = $this->kelasMilikPengajar($idKelas, (int) $request->user()->id_akun);

        $pilihanPeriode = array_key_exists((string) $request->query('periode'), ProgresController::PILIHAN_PERIODE) ? (string) $request->query('periode') : '30';
        $periode = Periode::dariPilihan($pilihanPeriode);

        $progres = app(ProgresController::class);
        $anggota = $progres->anggotaAktif($idKelas);
        $ringkasan = $progres->agregasiProgres(array_keys($anggota), $periode);

        $namaMateri = MateriAksara::query()
            ->whereIn('id_materi', array_keys($ringkasan['penguasaanMateri']))
            ->pluck('nama_materi', 'id_materi');

        $idPelajar = $request->integer('pelajar') ?: null;
        $detail = $idPelajar ? $this->ambilProgresAnggota($idKelas, $idPelajar, $periode) : null;

        return view('pemantauan.index', [
            'kelas' => $kelas,
            'anggota' => $anggota,
            'ringkasan' => $ringkasan,
            'namaMateri' => $namaMateri,
            'pilihanPeriode' => $pilihanPeriode,
            'idPelajarTerpilih' => $idPelajar,
            'detail' => $detail,
        ]);
    }

    /** ambilProgresAnggota(idKelas, idPelajar): Data (Algo-081) Hanya untuk anggota aktif kelas milik Pengajar. */
    public function ambilProgresAnggota(int $idKelas, int $idPelajar, ?Periode $periode = null): HasilOperasi
    {
        $this->kelasMilikPengajar($idKelas, (int) auth()->id());

        $progres = app(ProgresController::class);
        $anggota = $progres->anggotaAktif($idKelas);
        if (! array_key_exists($idPelajar, $anggota)) {
            Log::warning('Pemantauan ditolak: bukan anggota aktif', ['id_kelas' => $idKelas, 'id_pelajar' => $idPelajar, 'id_pengajar' => auth()->id()]);

            return HasilOperasi::gagal('BUKAN_ANGGOTA_AKTIF', 'Pengguna ini bukan anggota aktif kelas. Data progres tidak ditampilkan.');
        }

        return HasilOperasi::sukses([
            'idPelajar' => $idPelajar,
            'namaTampilan' => $anggota[$idPelajar],
            'ringkasan' => $progres->hitungProgres($idPelajar, $periode),
            'riwayatTerbaru' => RiwayatLatihan::query()
                ->with('materi:id_materi,nama_materi')
                ->where('id_pelajar', $idPelajar)
                ->when($periode, fn ($q) => $q->whereBetween('tanggal_pengerjaan', $periode->rentang()))
                ->orderByDesc('tanggal_pengerjaan')->orderByDesc('id_riwayat')
                ->limit(10)->get(),
        ]);
    }

    /** kirimUmpanBalik(idKelas, data: DataUmpanBalik): HasilOperasi (Algo-080) Sesuai DPPL, umpan balik disimpan di data_feedback dengan id_akun = Pelajar tujuan dan kategori MATERI. */
    public function kirimUmpanBalik(Request $request, int $idKelas): RedirectResponse
    {
        $kelas = $this->kelasMilikPengajar($idKelas, (int) $request->user()->id_akun);

        $data = $request->validate([
            'id_pelajar' => ['required', 'integer'],
            'pesan' => ['required', 'string', 'max:1000'],
        ], [
            'pesan.required' => 'Umpan balik tidak boleh kosong.',
            'pesan.max' => 'Umpan balik paling banyak 1000 karakter.',
        ]);

        $anggota = app(ProgresController::class)->anggotaAktif($idKelas);
        if (! array_key_exists((int) $data['id_pelajar'], $anggota)) {
            return back()->withErrors(['pesan' => 'Umpan balik hanya bisa dikirim ke anggota aktif kelas ini.']);
        }

        try {
            DB::table('data_feedback')->insert([
                'id_akun' => (int) $data['id_pelajar'],
                'kategori' => KategoriFeedback::Materi->value,
                'pesan' => sprintf('[Umpan balik Pengajar %s, kelas %s] %s', $request->user()->nama_tampilan, $kelas->nama, trim($data['pesan'])),
                'waktu_kirim' => now(),
                'status' => StatusFeedback::Baru->value,
            ]);
        } catch (\Throwable $e) {
            Log::error('Umpan balik gagal disimpan', ['id_kelas' => $idKelas, 'pesan_galat' => $e->getMessage()]);

            return back()->withInput()->withErrors(['pesan' => 'Umpan balik gagal disimpan. Silakan coba lagi.']);
        }

        return redirect()
            ->route('pemantauan.index', ['idKelas' => $idKelas, 'pelajar' => $data['id_pelajar']])
            ->with('status', 'Umpan balik terkirim ke '.$anggota[(int) $data['id_pelajar']].'.');
    }

    /** Memastikan kelas aktif dan milik Pengajar yang sedang masuk; selain itu 403. (Setara KelasBelajar.periksaPemilik milik modul Marvel; dapat diganti setelah tersedia.) */
    private function kelasMilikPengajar(int $idKelas, int $idPengajar): object
    {
        $kelas = DB::table('kelas_belajar')
            ->where('id_kelas', $idKelas)
            ->where('status', StatusKelas::Aktif->value)
            ->first();

        if (! $kelas || (int) $kelas->pemilik_id !== $idPengajar) {
            Log::warning('Akses pemantauan ditolak', ['id_kelas' => $idKelas, 'id_pengajar' => $idPengajar]);
            abort(403, 'Kelas tidak ditemukan atau bukan milik Anda.');
        }

        return $kelas;
    }
}
