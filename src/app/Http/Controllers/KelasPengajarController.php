<?php

namespace App\Http\Controllers;

use App\Enums\ModeLatihan;
use App\Enums\StatusPublikasi;
use App\Http\Requests\SimpanTugasRequest;
use App\Models\KelasBelajar;
use App\Models\MateriAksara;
use App\Services\Kelas\TugasKelasLayanan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

/**
 * Bagian C20 KelasController untuk pengelolaan kelas oleh Pengajar (UC-07), pemilik modul: Marvel.
 * Halaman: I-14 Kelola Kelas, I-15 Terbitkan Tugas, I-16 Kelola Anggota.
 * C20 dipecah tiga berkas (lihat PemantauanKelasController); catat di B.1 dan B.5.
 */
class KelasPengajarController extends Controller
{
    public function __construct(private readonly TugasKelasLayanan $tugas) {}

    /** I-14: daftar kelas milik Pengajar dan form kelas baru. */
    public function index(Request $request): View
    {
        return view('kelas-pengajar.index', [
            'daftarKelas' => KelasBelajar::milikPengajar((int) $request->user()->id_akun),
        ]);
    }

    /** buatKelas + buatKodeBergabung. */
    public function simpan(Request $request): RedirectResponse
    {
        $data = $request->validate(['nama' => ['required', 'string', 'max:100']], [
            'nama.required' => 'Nama kelas wajib diisi.',
            'nama.max' => 'Nama kelas paling banyak 100 karakter.',
        ]);

        $hasil = KelasBelajar::buatKelas((int) $request->user()->id_akun, $data['nama']);
        if (! $hasil->berhasil) {
            return back()->withInput()->withErrors(['nama' => $hasil->pesan]);
        }

        return redirect()->route('kelas.pengajar.tampil', $hasil->ambil('idKelas'))
            ->with('status', 'Kelas dibuat. Bagikan kode '.$hasil->ambil('kodeBergabung').' kepada Pelajar.');
    }

    /** Detail kelas: kode, scoreboard, tugas. */
    public function tampilkan(int $idKelas): View
    {
        $kelas = $this->kelasDikelola($idKelas);

        return view('kelas-pengajar.tampil', [
            'kelas' => $kelas,
            'jumlahAnggota' => count($kelas->anggotaAktif()),
            'daftarTugas' => $kelas->daftarTugas(),
            'scoreboard' => $kelas->ambilScoreboard(),
        ]);
    }

    public function gantiKode(int $idKelas): RedirectResponse
    {
        $hasil = $this->kelasDikelola($idKelas)->gantiKode();

        return $this->kembaliKeKelas($idKelas, $hasil);
    }

    public function ubahScoreboard(Request $request, int $idKelas): RedirectResponse
    {
        $hasil = $this->kelasDikelola($idKelas)->ubahScoreboard($request->boolean('aktif'));

        return $this->kembaliKeKelas($idKelas, $hasil);
    }

    /** I-16: daftar anggota. */
    public function anggota(int $idKelas): View
    {
        $kelas = $this->kelasDikelola($idKelas);

        return view('kelas-pengajar.anggota', ['kelas' => $kelas, 'anggota' => $kelas->daftarAnggota()]);
    }

    /** Algo-083 kelolaAnggota: status menjadi keluar, riwayat tidak dihapus. */
    public function keluarkanAnggota(int $idKelas, int $idPelajar): RedirectResponse
    {
        $hasil = $this->kelasDikelola($idKelas)->keluarkanAnggota($idPelajar);
        if (! $hasil->berhasil) {
            Log::warning('Pengeluaran anggota ditolak', ['id_kelas' => $idKelas, 'id_pelajar' => $idPelajar, 'kode' => $hasil->kode]);
        }

        return redirect()->route('kelas.pengajar.anggota', $idKelas)
            ->with($hasil->berhasil ? 'status' : 'galat', $hasil->pesan);
    }

    /** I-15: form tugas baru. */
    public function formTugas(int $idKelas): View
    {
        return view('kelas-pengajar.tugas-form', [
            'kelas' => $this->kelasDikelola($idKelas),
            'daftarMateri' => MateriAksara::query()->where('status_publikasi', StatusPublikasi::Terbit->value)
                ->orderBy('jenis_aksara')->orderBy('tingkat_kesulitan')->orderBy('nama_materi')->get(['id_materi', 'nama_materi']),
            'daftarMode' => ModeLatihan::cases(),
        ]);
    }

    public function simpanTugas(SimpanTugasRequest $request, int $idKelas): RedirectResponse
    {
        $kelas = $this->kelasDikelola($idKelas);
        $data = $request->validated();

        $hasil = $this->tugas->buatTugas($kelas, $data['instruksi'], $data['tenggat'], $data['komponen'], $data['aksi'] === 'terbitkan');
        if (! $hasil->berhasil) {
            return back()->withInput()->withErrors(['umum' => $hasil->pesan]);
        }

        return $this->kembaliKeKelas($idKelas, $hasil);
    }

    public function terbitkanTugas(int $idKelas, int $idTugas): RedirectResponse
    {
        $hasil = $this->tugas->terbitkan($this->kelasDikelola($idKelas), $idTugas);

        return $this->kembaliKeKelas($idKelas, $hasil);
    }

    /** 404 bila kelas tidak ada, 403 bila bukan milik Pengajar yang masuk. */
    private function kelasDikelola(int $idKelas): KelasBelajar
    {
        $kelas = KelasBelajar::query()->findOrFail($idKelas);
        Gate::authorize('kelola', $kelas);

        return $kelas;
    }

    private function kembaliKeKelas(int $idKelas, \App\Support\Progres\HasilOperasi $hasil): RedirectResponse
    {
        return redirect()->route('kelas.pengajar.tampil', $idKelas)
            ->with($hasil->berhasil ? 'status' : 'galat', $hasil->pesan);
    }
}
