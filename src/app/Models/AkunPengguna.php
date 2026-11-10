<?php

namespace App\Models;

use App\Enums\Peran;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * C01 AkunPengguna (pemilik modul: Ryuza). Versi minimal disiapkan Natan agar autentikasi
 * memakai tabel akun_pengguna sesuai DPPL.
 *
 * Kolom peran sengaja tetap berupa teks huruf kecil ('pelajar', 'pengajar', 'tim_materi'),
 * karena kode modul materi membandingkannya langsung sebagai teks.
 *
 * Operasi DPPL yang masih diisi pemilik modul: buatAkunBaru, cariDariEmail, getProfil,
 * getDetailAkun, verifikasiHakAkses, ambilNamaTampilan. Catatan: begitu verifikasiHakAkses
 * ditambahkan, MateriController akan memakainya untuk memeriksa hak akses.
 */
class AkunPengguna extends Authenticatable
{
    use HasFactory;

    protected $table = 'akun_pengguna';

    protected $primaryKey = 'id_akun';

    protected $fillable = ['email', 'username', 'password_hash', 'peran', 'nama_tampilan'];

    /** Hash kata sandi tidak pernah ikut dikirim ke tampilan atau JSON. */
    protected $hidden = ['password_hash'];

    /** Kolom kata sandi pada tabel ini bernama password_hash, bukan password. */
    public function getAuthPassword(): string
    {
        return $this->password_hash;
    }

    public function getAuthPasswordName(): string
    {
        return 'password_hash';
    }

    /** Tabel akun_pengguna tidak memiliki kolom remember_token (fitur "ingat saya" tidak ada di SKPL). */
    public function getRememberTokenName(): string
    {
        return '';
    }

    /** Memeriksa apakah akun memiliki salah satu peran yang diberikan. */
    public function memilikiPeran(Peran|string ...$peran): bool
    {
        $daftar = array_map(fn ($p) => $p instanceof Peran ? $p->value : strtolower($p), $peran);

        return in_array(strtolower((string) $this->peran), $daftar, true);
    }

    public function motivasi(): HasOne
    {
        return $this->hasOne(MotivasiBelajar::class, 'id_pelajar', 'id_akun');
    }

    /** Huruf awal nama tampilan, dipakai untuk avatar. */
    public function inisial(): string
    {
        return mb_strtoupper(mb_substr((string) $this->nama_tampilan, 0, 1));
    }
}
