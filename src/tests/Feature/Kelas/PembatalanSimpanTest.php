<?php

namespace Tests\Feature\Kelas;

use App\Models\KeanggotaanKelas;
use App\Models\KomponenTugas;
use App\Models\TugasKelas;
use Tests\Support\SkemaKelas;

class PembatalanSimpanTest extends SkemaKelas
{
    public function test_komponen_ditolak_membatalkan_seluruh_draf(): void
    {
        KomponenTugas::creating(fn () => false);
        try {
            $hasil = (new TugasKelas)->simpan($this->dataTugas());
            $this->assertFalse($hasil->berhasil);
            $this->assertDatabaseCount('tugas_kelas', 0);
            $this->assertDatabaseCount('komponen_tugas', 0);
        } finally {
            KomponenTugas::flushEventListeners();
        }
    }

    public function test_revisi_ditolak_mempertahankan_komponen_lama(): void
    {
        $id = (new TugasKelas)->simpan($this->dataTugas())->data['id_tugas'];
        $lama = TugasKelas::findOrFail($id)->komponen->modelKeys();
        TugasKelas::updating(fn () => false);
        try {
            $hasil = (new TugasKelas)->simpan($this->dataTugas([
                'id_tugas' => $id, 'instruksi' => 'Revisi ditolak',
                'komponen_tugas' => [['jenis' => 'materi', 'id_materi' => 1, 'wajib' => true]],
            ]));
            $this->assertFalse($hasil->berhasil);
            $this->assertSame($lama, TugasKelas::findOrFail($id)->komponen->modelKeys());
            $this->assertDatabaseHas('tugas_kelas', ['id_tugas' => $id, 'instruksi' => 'Pelajari nglegena lalu latihan menulis.']);
        } finally {
            TugasKelas::flushEventListeners();
        }
    }

    public function test_penerbitan_ditolak_tidak_melaporkan_sukses(): void
    {
        $id = (new TugasKelas)->simpan($this->dataTugas())->data['id_tugas'];
        TugasKelas::updating(fn () => false);
        try {
            $this->assertFalse(TugasKelas::findOrFail($id)->terbitkan()->berhasil);
            $this->assertDatabaseHas('tugas_kelas', ['id_tugas' => $id, 'status' => 'draf']);
        } finally {
            TugasKelas::flushEventListeners();
        }
    }

    public function test_gabung_ditolak_tidak_melaporkan_sukses(): void
    {
        KeanggotaanKelas::creating(fn () => false);
        try {
            $this->assertFalse((new KeanggotaanKelas)->simpanKeanggotaan(1, 1)->berhasil);
            $this->assertDatabaseCount('keanggotaan_kelas', 0);
        } finally {
            KeanggotaanKelas::flushEventListeners();
        }
    }

    public function test_perubahan_status_ditolak_tidak_melaporkan_sukses(): void
    {
        (new KeanggotaanKelas)->simpanKeanggotaan(1, 1);
        $anggota = KeanggotaanKelas::firstOrFail();
        KeanggotaanKelas::updating(fn () => false);
        try {
            $this->assertFalse($anggota->ubahStatus('keluar')->berhasil);
            $this->assertSame('aktif', $anggota->fresh()->status);
        } finally {
            KeanggotaanKelas::flushEventListeners();
        }
    }

    public function test_aktivasi_ulang_ditolak_tidak_melaporkan_sukses(): void
    {
        (new KeanggotaanKelas)->simpanKeanggotaan(1, 1);
        KeanggotaanKelas::firstOrFail()->ubahStatus('keluar');
        KeanggotaanKelas::updating(fn () => false);
        try {
            $this->assertFalse((new KeanggotaanKelas)->simpanKeanggotaan(1, 1)->berhasil);
            $this->assertDatabaseHas('keanggotaan_kelas', ['id_pelajar' => 1, 'status' => 'keluar']);
        } finally {
            KeanggotaanKelas::flushEventListeners();
        }
    }
}
