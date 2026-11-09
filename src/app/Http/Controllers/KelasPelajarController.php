<?php

namespace App\Http\Controllers;

use App\Models\KeanggotaanKelas;
use App\Models\PenyelesaianTugas;
use App\Models\TugasKelas;
use App\Services\PengumpulanTugas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

class KelasPelajarController extends Controller
{
    private function pelajar(Request $r): int
    {
        abort_unless($r->user(), 401);
        abort_unless(strtolower((string) $r->user()->peran) === 'pelajar', 403);

        return (int) $r->user()->getAuthIdentifier();
    }

    private function akses(Request $r, int $kelas): int
    {
        $id = $this->pelajar($r);
        abort_unless(KeanggotaanKelas::periksaAkses($id, $kelas) && DB::table('kelas_belajar')->where('id_kelas', $kelas)->whereRaw('LOWER(status) = ?', ['aktif'])->exists(), 404);

        return $id;
    }

    public function index(Request $r, ?int $kelas = null)
    {
        $id = $this->pelajar($r);
        $daftar = DB::table('kelas_belajar as k')->join('keanggotaan_kelas as a', 'a.id_kelas', '=', 'k.id_kelas')->where('a.id_pelajar', $id)->where('a.status', 'aktif')->whereRaw('LOWER(k.status) = ?', ['aktif'])->select('k.*')->orderBy('k.nama')->get();
        if ($kelas !== null) {
            $this->akses($r, $kelas);
        }$kelas ??= $daftar->first()?->id_kelas;
        $tugas = $kelas ? TugasKelas::ambilTugasAktif($kelas) : collect();
        $status = PenyelesaianTugas::ambilStatus($id, $tugas->pluck('id_tugas')->all())->keyBy('id_tugas');

        return view('kelas-pelajar.index', compact('daftar', 'kelas', 'tugas', 'status'));
    }

    public function gabung(Request $r)
    {
        $id = $this->pelajar($r);
        $data = $r->validate(['kode' => ['required', 'string', 'regex:/^[A-Za-z0-9]{6}$/']]);
        $kelas = DB::table('kelas_belajar')->where('kode_bergabung', strtoupper($data['kode']))->whereRaw('LOWER(status) = ?', ['aktif'])->first();
        if (! $kelas) {
            return back()->withErrors(['kode' => 'Kode tidak ditemukan atau kelas tidak aktif.'])->withInput();
        }$hasil = (new KeanggotaanKelas)->simpanKeanggotaan($id, $kelas->id_kelas);
        if (! $hasil->berhasil) {
            return back()->withErrors(['kode' => $hasil->pesan])->withInput();
        }

return redirect()->route('kelas-pelajar.kelas', $kelas->id_kelas)->with('sukses', $hasil->pesan);
    }

    public function detail(Request $r, int $kelas, int $tugas, PengumpulanTugas $layanan)
    {
        $id = $this->akses($r, $kelas);
        $detail = TugasKelas::ambilDetail($kelas, $tugas);
        abort_unless($detail, 404);
        $progres = $layanan->progres($detail, $id);
        $hasil = PenyelesaianTugas::ambilStatus($id, [$tugas])->first();

        return view('kelas-pelajar.detail', compact('detail', 'kelas', 'progres', 'hasil'));
    }

    public function kumpulkan(Request $r, int $kelas, int $tugas, PengumpulanTugas $layanan)
    {
        $id = $this->akses($r, $kelas);
        abort_unless(TugasKelas::ambilDetail($kelas, $tugas), 404);
        $hasil = $layanan->simpanPengumpulan($kelas, $tugas, $id);

        return $hasil->berhasil ? back()->with('sukses', $hasil->pesan.' Status: '.str_replace('_', ' ', $hasil->data['status'])) : back()->withErrors(['pengumpulan' => $hasil->pesan]);
    }

    public function kerjakan(Request $r, int $kelas, int $tugas, int $komponen)
    {
        $this->akses($r, $kelas);
        $t = TugasKelas::ambilDetail($kelas, $tugas);
        abort_unless($t, 404);
        $c = $t->komponen->firstWhere('id_komponen', $komponen);
        abort_unless($c, 404);
        $name = $c->jenis === 'materi' ? 'materi.show' : 'latihan.pilih-mode';
        if (! Route::has($name)) {
            return back()->withErrors(['modul' => 'Modul pengerjaan belum terpasang. Hubungi pengelola kelas.']);
        }

return redirect()->route($name,[$c->id_materi, 'id_tugas' => $tugas, 'id_komponen' => $komponen, 'mode' => $c->mode]);
    }
}
