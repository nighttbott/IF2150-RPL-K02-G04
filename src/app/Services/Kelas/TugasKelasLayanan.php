<?php

namespace App\Services\Kelas;

use App\Enums\ModeLatihan;
use App\Enums\StatusKelas;
use App\Enums\StatusPublikasi;
use App\Enums\StatusTugas;
use App\Models\KelasBelajar;
use App\Models\MateriAksara;
use App\Support\Progres\HasilOperasi;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

/**
 * Penerbitan tugas oleh Pengajar (UC-07, pemilik: Marvel).
 *
 * Sementara bekerja langsung pada tabel tugas_kelas dan komponen_tugas. Bila model TugasKelas
 * milik Abduh sudah digabung, isi buatTugas dan terbitkan dapat diganti pemanggilan model itu
 * tanpa mengubah controller (tanda tangan method tetap).
 */
final class TugasKelasLayanan
{
    /**
     * Membuat tugas berstatus draf, atau langsung terbit bila $terbitkan = true.
     * Semua dilakukan dalam satu transaksi. Data hasil: idTugas, status.
     *
     * @param  list<array{jenis:string, id_materi:int|string, mode?:?string, wajib?:mixed}>  $komponen
     */
    public function buatTugas(KelasBelajar $kelas, string $instruksi, string $tenggat, array $komponen, bool $terbitkan): HasilOperasi
    {
        if ($kelas->status !== StatusKelas::Aktif) {
            return HasilOperasi::gagal('KELAS_NONAKTIF', 'Kelas tidak aktif.');
        }
        $instruksi = trim($instruksi);
        if ($instruksi === '') {
            return HasilOperasi::gagal('INSTRUKSI_KOSONG', 'Instruksi tugas wajib diisi.');
        }
        try {
            $waktu = CarbonImmutable::parse($tenggat);
        } catch (\Throwable) {
            return HasilOperasi::gagal('TENGGAT_TIDAK_VALID', 'Format tenggat tidak valid.');
        }
        if ($waktu->lessThanOrEqualTo(now())) {
            return HasilOperasi::gagal('TENGGAT_LAMPAU', 'Tenggat harus lebih akhir dari waktu sekarang.');
        }

        $baris = $this->normalisasiKomponen($komponen);
        if (is_string($baris)) {
            return HasilOperasi::gagal(...explode('|', $baris, 2));
        }

        $status = $terbitkan ? StatusTugas::Terbit : StatusTugas::Draf;

        $idTugas = DB::transaction(function () use ($kelas, $instruksi, $waktu, $baris, $status) {
            $id = DB::table('tugas_kelas')->insertGetId([
                'id_kelas' => $kelas->id_kelas,
                'instruksi' => $instruksi,
                'tenggat' => $waktu->format('Y-m-d H:i:s'),
                'status' => $status->value,
            ], 'id_tugas');

            DB::table('komponen_tugas')->insert(array_map(fn ($k) => $k + ['id_tugas' => $id], $baris));

            return $id;
        });

        return HasilOperasi::sukses(['idTugas' => (int) $idTugas, 'status' => $status->value], $terbitkan ? 'Tugas diterbitkan.' : 'Tugas disimpan sebagai draf.');
    }

    /** Menerbitkan tugas draf milik kelas ini. Tugas yang sudah terbit dibiarkan (idempoten). */
    public function terbitkan(KelasBelajar $kelas, int $idTugas): HasilOperasi
    {
        $tugas = DB::table('tugas_kelas')->where('id_tugas', $idTugas)->where('id_kelas', $kelas->id_kelas)->first();
        if ($tugas === null) {
            return HasilOperasi::gagal('TUGAS_TIDAK_DITEMUKAN', 'Tugas tidak ditemukan di kelas ini.');
        }
        if ($tugas->status === StatusTugas::Terbit->value) {
            return HasilOperasi::sukses(['idTugas' => $idTugas], 'Tugas sudah terbit.');
        }
        if (! DB::table('komponen_tugas')->where('id_tugas', $idTugas)->where('wajib', true)->exists()) {
            return HasilOperasi::gagal('TANPA_KOMPONEN_WAJIB', 'Tugas belum punya komponen wajib.');
        }
        if (CarbonImmutable::parse($tugas->tenggat)->lessThanOrEqualTo(now())) {
            return HasilOperasi::gagal('TENGGAT_LAMPAU', 'Tenggat tugas sudah lewat. Buat tugas baru dengan tenggat yang baru.');
        }

        DB::table('tugas_kelas')->where('id_tugas', $idTugas)->update(['status' => StatusTugas::Terbit->value]);

        return HasilOperasi::sukses(['idTugas' => $idTugas], 'Tugas diterbitkan.');
    }

    /**
     * @return list<array<string, mixed>>|string  baris siap simpan, atau "KODE|pesan" bila tidak valid
     */
    private function normalisasiKomponen(array $komponen): array|string
    {
        if ($komponen === []) {
            return 'KOMPONEN_KOSONG|Tambahkan minimal satu komponen tugas.';
        }

        $idMateri = array_values(array_unique(array_map(fn ($k) => (int) ($k['id_materi'] ?? 0), $komponen)));
        $tersedia = MateriAksara::query()
            ->whereIn('id_materi', $idMateri)
            ->where('status_publikasi', StatusPublikasi::Terbit->value)
            ->pluck('id_materi')->map(fn ($i) => (int) $i)->all();

        $hasil = [];
        $adaWajib = false;
        foreach ($komponen as $k) {
            $jenis = (string) ($k['jenis'] ?? '');
            if (! in_array($jenis, ['materi', 'latihan'], true)) {
                return 'JENIS_TIDAK_VALID|Jenis komponen harus materi atau latihan.';
            }
            if (! in_array((int) ($k['id_materi'] ?? 0), $tersedia, true)) {
                return 'MATERI_TIDAK_TERSEDIA|Komponen hanya boleh memakai materi yang sudah terbit.';
            }

            $mode = null;
            if ($jenis === 'latihan') {
                $mode = ModeLatihan::tryFrom((string) ($k['mode'] ?? ''))?->value;
                if ($mode === null) {
                    return 'MODE_TIDAK_VALID|Komponen latihan harus memilih mode latihan.';
                }
            }

            $wajib = filter_var($k['wajib'] ?? true, FILTER_VALIDATE_BOOLEAN);
            $adaWajib = $adaWajib || $wajib;
            $hasil[] = ['jenis' => $jenis, 'id_materi' => (int) $k['id_materi'], 'mode' => $mode, 'wajib' => $wajib];
        }

        if (! $adaWajib) {
            return 'TANPA_KOMPONEN_WAJIB|Minimal satu komponen harus wajib.';
        }

        return $hasil;
    }
}
