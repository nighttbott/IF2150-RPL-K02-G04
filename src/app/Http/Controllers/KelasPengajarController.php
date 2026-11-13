<?php

namespace App\Http\Controllers;

use App\Enums\StatusKeanggotaan;
use App\Enums\StatusKelas;
use App\Models\AkunPengguna;
use App\Models\KeanggotaanKelas;
use App\Models\KelasBelajar;
use App\Models\MateriAksara;
use App\Models\MotivasiBelajar;
use App\Models\TugasKelas;
use App\Support\HakAkses;
use App\Support\Materi\FilterMateri;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/** C20 KelasController, bagian pengelolaan Pengajar (UC-07): buatKelas (Algo-071), buatTugas (Algo-072), terbitkanTugas (Algo-073), ambilDaftarAnggota (Algo-074), kelolaAnggota (Algo-075), gantiKodeKelas (Algo-076), aturScoreboard (Algo-077), ambilKelasPengajar (Algo-078). */
class KelasPengajarController extends Controller
{
    private const JENIS = [
        'materi' => 'Materi',
        'menulis' => 'Latihan menulis',
        'bunyi' => 'Latihan bunyi',
        'merangkai' => 'Latihan merangkai',
        'transliterasi' => 'Latihan transliterasi',
    ];

    /** I-14. Algo-078 ambilKelasPengajar */
    public function index(Request $request): View
    {
        $this->izinkan($request, 'buatKelas');

        $daftarKelas = KelasBelajar::daftarKelasPengajar($request->user()->getKey());
        $idKelas = $daftarKelas->pluck('id_kelas')->all();
        // Hitung anggota aktif dan tugas terbit untuk semua kelas sekaligus (hindari N+1).
        $anggota = KeanggotaanKelas::query()->whereIn('id_kelas', $idKelas)->where('status', StatusKeanggotaan::Aktif->value)
            ->selectRaw('id_kelas, COUNT(*) AS jumlah')->groupBy('id_kelas')->pluck('jumlah', 'id_kelas');
        $tugas = TugasKelas::query()->whereIn('id_kelas', $idKelas)->where('status', 'terbit')
            ->selectRaw('id_kelas, COUNT(*) AS jumlah')->groupBy('id_kelas')->pluck('jumlah', 'id_kelas');
        $daftarKelas->each(function (KelasBelajar $kelas) use ($anggota, $tugas) {
            $kelas->jumlah_anggota = (int) ($anggota[$kelas->id_kelas] ?? 0);
            $kelas->jumlah_tugas = (int) ($tugas[$kelas->id_kelas] ?? 0);
        });

        return view('kelas-pengajar.index', ['daftarKelas' => $daftarKelas]);
    }

    /** I-14-02. Algo-071 buatKelas */
    public function buat(Request $request): RedirectResponse
    {
        $this->izinkan($request, 'buatKelas');

        $request->validate(
            ['nama' => ['required', 'string', 'max:100']],
            [
                'nama.required' => 'Nama kelas wajib diisi.',
                'nama.max' => 'Nama kelas maksimal 100 karakter.',
            ],
        );

        $hasil = (new KelasBelajar)->simpan([
            'nama' => $request->input('nama'),
            'pemilik_id' => $request->user()->getKey(),
        ]);

        if (! $hasil->berhasil) {
            if ($hasil->kode === 'GAGAL_SIMPAN') {
                Log::error('Gagal membuat kelas', ['pemilik_id' => $request->user()->getKey()]);
            }

            return back()->withInput()->withErrors(['nama' => $hasil->pesan]);
        }

        return redirect()->route('pengajar.kelas.index')->with('sukses', [
            'judul' => 'Kelas "'.$request->input('nama').'" berhasil dibuat',
            'isi' => 'Bagikan kode bergabung kepada Pelajar.',
        ]);
    }

    /** I-14-04. Algo-076 gantiKodeKelas */
    public function gantiKode(Request $request, int $idKelas): RedirectResponse
    {
        // Kepemilikan diperiksa lagi di KelasBelajar::gantiKodeBergabung() (pertahanan berlapis).
        $kelas = $this->kelasMilikku($request, $idKelas, 'gantiKode');

        $hasil = $kelas->gantiKodeBergabung($request->user()->getKey());

        if (! $hasil->berhasil) {
            if ($hasil->kode === 'GAGAL_SIMPAN') {
                Log::error('Gagal mengganti kode kelas', ['id_kelas' => $idKelas]);
            }

            return back()->withErrors(['kode' => $hasil->pesan]);
        }

        return redirect()->route('pengajar.kelas.index')->with('sukses', [
            'judul' => 'Kode baru untuk "'.$kelas->nama.'": '.$hasil->data['kodeBergabung'],
            'isi' => 'Kode lama tidak berlaku lagi. Bagikan kode baru kepada Pelajar.',
        ]);
    }

    /** I-16. Algo-074 ambilDaftarAnggota + papan peringkat */
    public function anggota(Request $request, int $idKelas): View
    {
        $kelas = $this->kelasMilikku($request, $idKelas, 'lihatAnggota');
        $anggota = $this->profilAnggota($idKelas);
        $papan = $kelas->status_scoreboard
            ? MotivasiBelajar::susunScoreboard(array_column($anggota, 'namaTampilan', 'idAkun')) // Algo-031
            : [];

        return view('kelas-pengajar.anggota', compact('kelas', 'anggota', 'papan'));
    }

    /** I-16-02/03. Algo-075 kelolaAnggota (status nonaktif; riwayat tidak dihapus) */
    public function keluarkan(Request $request, int $idKelas, int $idPelajar): RedirectResponse
    {
        $this->kelasMilikku($request, $idKelas, 'kelolaAnggota');

        $keanggotaan = KeanggotaanKelas::cari($idKelas, $idPelajar);
        if ($keanggotaan === null || $keanggotaan->status !== StatusKeanggotaan::Aktif->value) {
            return back()->withErrors(['anggota' => 'Pelajar bukan anggota kelas ini.']);
        }

        $hasil = $keanggotaan->ubahStatus('nonaktif');
        if (! $hasil->berhasil) {
            Log::error('Gagal mengubah status keanggotaan', ['id_kelas' => $idKelas, 'id_pelajar' => $idPelajar]);

            return back()->withErrors(['anggota' => $hasil->pesan ?? 'Anggota gagal dikeluarkan.']);
        }

        return redirect()->route('pengajar.kelas.anggota', $idKelas)->with('sukses', [
            'judul' => 'Anggota dikeluarkan',
            'isi' => 'Akses ke kelas berakhir. Riwayat belajar tidak dihapus.',
        ]);
    }

    /** I-16-05. Algo-077 aturScoreboard */
    public function scoreboard(Request $request, int $idKelas): RedirectResponse
    {
        $kelas = $this->kelasMilikku($request, $idKelas, 'aturScoreboard');
        $request->validate(['aktif' => ['required', 'boolean']]);

        $hasil = $kelas->aturScoreboard($request->boolean('aktif'));
        if (! $hasil->berhasil) {
            return back()->withErrors(['scoreboard' => $hasil->pesan]);
        }

        return redirect()->route('pengajar.kelas.anggota', $idKelas);
    }

    /** I-15 */
    public function formTugas(Request $request, int $idKelas): View
    {
        $kelas = $this->kelasMilikku($request, $idKelas, 'buatTugas');

        return view('kelas-pengajar.tugas', [
            'kelas' => $kelas,
            'daftarMateri' => $this->daftarMateri(),
            'jenis' => self::JENIS,
        ]);
    }

    /** I-15-05. Algo-072 buatTugas lalu Algo-073 terbitkanTugas. Desain layar hanya punya satu tombol "Terbitkan", jadi draf disimpan lalu langsung diterbitkan. */
    public function terbitkan(Request $request, int $idKelas): RedirectResponse
    {
        $kelas = $this->kelasMilikku($request, $idKelas, 'buatTugas');

        $data = $request->validate([
            'instruksi' => ['required', 'string', 'max:2000'],
            'tenggat' => ['required', 'date', 'after:now'],
            'komponen' => ['required', 'array', 'min:1'],
            'komponen.*.materi' => ['required', Rule::in(array_column($this->daftarMateri(), 'id'))],
            'komponen.*.jenis' => ['required', Rule::in(array_keys(self::JENIS))],
            'komponen.*.wajib' => ['required', 'boolean'],
            'komponen.*.materi_label' => ['nullable', 'string', 'max:100'],
            'komponen.*.jenis_label' => ['nullable', 'string', 'max:100'],
        ], [
            'instruksi.required' => 'Instruksi tugas wajib diisi.',
            'tenggat.required' => 'Tenggat wajib diisi.',
            'tenggat.after' => 'Tenggat harus lebih akhir dari sekarang.',
            'komponen.required' => 'Tambahkan minimal satu komponen tugas.',
            'komponen.min' => 'Tambahkan minimal satu komponen tugas.',
        ]);

        $draf = (new TugasKelas)->simpan([
            'id_kelas' => $kelas->id_kelas,
            'instruksi' => $data['instruksi'],
            'tenggat' => $data['tenggat'],
            'komponen_tugas' => array_map(fn (array $k): array => [
                'jenis' => $k['jenis'] === 'materi' ? 'materi' : 'latihan',
                'id_materi' => (int) $k['materi'],
                'mode' => $k['jenis'] === 'materi' ? null : $k['jenis'],
                'wajib' => (bool) $k['wajib'],
            ], array_values($data['komponen'])),
        ]);
        if (! $draf->berhasil) {
            return back()->withInput()->withErrors(['tugas' => $draf->pesan ?? 'Tugas gagal disimpan.']);
        }

        $this->izinkan($request, 'terbitkanTugas');
        $tugas = TugasKelas::find($draf->data['id_tugas'] ?? null);
        $hasil = $tugas?->terbitkan();
        if ($hasil === null || ! $hasil->berhasil) {
            Log::error('Gagal menerbitkan tugas', ['id_kelas' => $idKelas]);

            return back()->withInput()->withErrors(['tugas' => $hasil?->pesan ?? 'Tugas gagal diterbitkan.']);
        }

        $jumlah = count($this->profilAnggota($idKelas));

        return redirect()->route('pengajar.kelas.index')->with('sukses', [
            'judul' => "Tugas diterbitkan untuk {$jumlah} anggota aktif",
            'isi' => 'Tugas hanya terlihat oleh anggota aktif kelas "'.$kelas->nama.'".',
        ]);
    }

    // ------------------------------------------------------------------ pembantu

    private function izinkan(Request $request, string $aksi): void
    {
        abort_unless(HakAkses::izinkan($request->user(), $aksi), 403, 'Halaman ini khusus Pengajar.');
    }

    private function kelasMilikku(Request $request, int $idKelas, string $aksi): KelasBelajar
    {
        $this->izinkan($request, $aksi);

        $kelas = KelasBelajar::find($idKelas);
        abort_if($kelas === null || $kelas->status !== StatusKelas::Aktif->value, 404, 'Kelas tidak ditemukan.');
        abort_unless($kelas->periksaPemilik($request->user()->getKey()), 403, 'Anda bukan pemilik kelas ini.');

        return $kelas;
    }

    /** Profil anggota aktif untuk I-16. @return list<array{idAkun:int, namaTampilan:string}> */
    private function profilAnggota(int $idKelas): array
    {
        $idPelajar = KeanggotaanKelas::daftarAnggotaAktif($idKelas)->pluck('id_pelajar')->all();

        return AkunPengguna::query()
            ->whereIn('id_akun', $idPelajar)
            ->orderBy('nama_tampilan')
            ->get(['id_akun', 'nama_tampilan'])
            ->map(fn (AkunPengguna $a) => ['idAkun' => (int) $a->id_akun, 'namaTampilan' => $a->nama_tampilan])
            ->all();
    }

    /** Materi terbit yang dapat dijadikan komponen tugas (MateriAksara.ambilDaftarMateri, Algo-040). */
    private function daftarMateri(): array
    {
        return array_map(
            fn (MateriAksara $m) => ['id' => (int) $m->id_materi, 'nama' => $m->nama_materi],
            MateriAksara::ambilDaftarMateri(new FilterMateri),
        );
    }
}
