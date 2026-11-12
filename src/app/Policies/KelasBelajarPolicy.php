<?php

namespace App\Policies;

use App\Enums\Peran;
use App\Models\AkunPengguna;
use App\Models\KelasBelajar;

/**
 * Hak akses atas kelas (Algo-083 kelolaAnggota, pemilik modul: Marvel).
 * Hanya Pengajar pemilik kelas yang boleh mengelola kelas, anggota, dan tugasnya.
 */
class KelasBelajarPolicy
{
    public function kelola(AkunPengguna $akun, KelasBelajar $kelas): bool
    {
        return $akun->memilikiPeran(Peran::Pengajar) && (int) $kelas->pemilik_id === (int) $akun->id_akun;
    }
}
