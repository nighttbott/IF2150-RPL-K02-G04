<?php

namespace App\Http\Controllers;

use App\Models\KeanggotaanKelas;
use App\Models\MateriAksara;
use App\Models\MotivasiBelajar;
use App\Models\PenyelesaianTugas;
use App\Models\TugasKelas;
use App\Services\PengumpulanTugas;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
        }
        $kelas ??= $daftar->first()?->id_kelas;
        $tugas = $kelas ? TugasKelas::ambilTugasAktif($kelas) : collect();
        $status = PenyelesaianTugas::ambilStatus($id, $tugas->pluck('id_tugas')->all())->keyBy('id_tugas');

        $motivasi = MotivasiBelajar::ambilMotivasi($id);

        return view('kelas-pelajar.index', compact('daftar', 'kelas', 'tugas', 'status', 'motivasi'));
    }

    public function gabung(Request $r)
    {
        $id = $this->pelajar($r);
        $data = $r->validate(['kode' => ['required', 'string', 'regex:/^[A-Za-z0-9]{6}$/']]);
        $kelas = DB::table('kelas_belajar')->where('kode_bergabung', strtoupper($data['kode']))->whereRaw('LOWER(status) = ?', ['aktif'])->first();
        if (! $kelas) {
            return back()->withErrors(['kode' => 'Kode tidak ditemukan atau kelas tidak aktif.'])->withInput();
        }
        $hasil = (new KeanggotaanKelas)->simpanKeanggotaan($id, $kelas->id_kelas);
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
        $namaMateri = MateriAksara::query()->whereIn('id_materi', $detail->komponen->pluck('id_materi'))->pluck('nama_materi', 'id_materi');
        $namaKelas = DB::table('kelas_belajar')->where('id_kelas', $kelas)->value('nama');

        return view('kelas-pelajar.detail', compact('detail', 'kelas', 'progres', 'hasil', 'namaMateri', 'namaKelas'));
    }

    public function kumpulkan(Request $r, int $kelas, int $tugas, PengumpulanTugas $layanan)
    {
        $id = $this->akses($r, $kelas);
        abort_unless(TugasKelas::ambilDetail($kelas, $tugas), 404);
        $hasil = $layanan->simpanPengumpulan($kelas, $tugas, $id);

        return $hasil->berhasil
            ? back()->with('sukses', $hasil->pesan.' Status: '.str_replace('_', ' ', $hasil->data['status']))->with('baru_dikumpulkan', $hasil->data['status'])
            : back()->withErrors(['pengumpulan' => $hasil->pesan]);
    }

    public function kerjakan(Request $r, int $kelas, int $tugas, int $komponen)
    {
        $this->akses($r, $kelas);
        $t = TugasKelas::ambilDetail($kelas, $tugas);
        abort_unless($t, 404);
        $c = $t->komponen->firstWhere('id_komponen', $komponen);
        abort_unless($c, 404);
        if ($c->jenis === 'materi') {
            $paket = app(MateriController::class)->muatPaketMateri($c->id_materi, (string) config('ngaksara.versi_aplikasi'));
            abort_if($paket->galat === 'MATERI_TIDAK_TERSEDIA', 404);

            return view('materi.show', [
                'paket' => $paket,
                'konteksTugas' => [
                    'kembali' => route('kelas-pelajar.detail', [$kelas, $tugas]),
                    'selesai' => route('kelas-pelajar.selesai-materi', [$kelas, $tugas, $komponen]),
                ],
            ]);
        }
        // Komponen latihan dibuka langsung pada mode yang ditentukan Pengajar, membawa konteks tugas.
        return redirect()->route('latihan.soal', [$c->id_materi, $c->mode, 'tugas' => $tugas, 'komponen' => $komponen]);
    }

    public function selesaikanMateri(Request $r, int $kelas, int $tugas, int $komponen, PengumpulanTugas $layanan): RedirectResponse
    {
        $id = $this->akses($r, $kelas);
        $detail = TugasKelas::ambilDetail($kelas, $tugas);
        abort_unless($detail, 404);
        $item = $detail->komponen->firstWhere('id_komponen', $komponen);
        abort_unless($item && $item->jenis === 'materi', 404);
        $hasil = $layanan->simpanPenyelesaianMateri($kelas, $tugas, $komponen, $id);
        $balasan = redirect()->route('kelas-pelajar.detail', [$kelas, $tugas]);

        return $hasil->berhasil ? $balasan->with('sukses', $hasil->pesan) : $balasan->withErrors(['materi' => $hasil->pesan]);
    }
}
