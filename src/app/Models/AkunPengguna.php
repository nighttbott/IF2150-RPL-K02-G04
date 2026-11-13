<?php

namespace App\Models;

use App\Enums\Peran;
use App\Support\DataPendaftaran;
use App\Support\HasilOperasi;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/** C01 AkunPengguna — Model pengguna utama Ngaksara. Menyimpan kredensial, profil, dan peran Pelajar, Pengajar, atau Tim Materi, serta menjadi acuan hak akses seluruh fitur. */
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

    /** Menyimpan akun baru. data->passwordHash harus sudah di-hash oleh pemanggil (OtentikasiController.hashPassword()). */
    public function buatAkunBaru(DataPendaftaran $data): HasilOperasi
    {
        $email = strtolower(trim($data->email));

        // periksa email & username
        $cek = DB::selectOne(
            'SELECT
                COALESCE(SUM(email = :email), 0)    AS email_dipakai,
                COALESCE(SUM(username = :username), 0) AS username_dipakai
             FROM akun_pengguna
             WHERE email = :email2 OR username = :username2',
            [
                'email'     => $email,
                'username'  => $data->username,
                'email2'    => $email,
                'username2' => $data->username,
            ],
        );

        if ((int) ($cek->email_dipakai ?? 0) > 0) {
            return HasilOperasi::gagal('EMAIL_TERDAFTAR', 'Email sudah terdaftar');
        }
        if ((int) ($cek->username_dipakai ?? 0) > 0) {
            return HasilOperasi::gagal('USERNAME_TERPAKAI', 'Username sudah digunakan');
        }

        $namaTampilan = $data->namaTampilan ?? $data->username;

        try {
            // simpan akun baru
            DB::statement(
                'INSERT INTO akun_pengguna (email, username, password_hash, peran, nama_tampilan, created_at)
                 VALUES (:email, :username, :password_hash, :peran, :nama_tampilan, CURRENT_TIMESTAMP)',
                [
                    'email'         => $email,
                    'username'      => $data->username,
                    'password_hash' => $data->passwordHash,
                    'peran'         => $data->peran->value,
                    'nama_tampilan' => $namaTampilan,
                ],
            );
            $idBaru = (int) DB::getPdo()->lastInsertId();
        } catch (\Illuminate\Database\UniqueConstraintViolationException) {
            // dua pendaftaran bersamaan dengan data sama (pelanggaran indeks UNIQUE)
            return HasilOperasi::gagal('AKUN_SUDAH_ADA', 'Email atau username sudah digunakan');
        }

        $this->id_akun       = $idBaru;
        $this->email         = $email;
        $this->username      = $data->username;
        $this->password_hash = $data->passwordHash;
        $this->peran         = $data->peran->value;
        $this->nama_tampilan = $namaTampilan;

        // tandai objek sebagai sudah tersimpan
        $this->exists = true;
        $this->wasRecentlyCreated = true;
        $this->syncOriginal();

        return HasilOperasi::sukses(['id_akun' => $idBaru]);
    }

    /** Mengambil akun berdasarkan email untuk proses login. Mengembalikan null jika tidak ada. */
    public static function cariBerdasarkanEmail(string $email): ?static
    {
        return static::query()
            ->where('email', strtolower(trim($email)))
            ->first();
    }

    /** Memeriksa kata sandi terhadap passwordHash tanpa mengeluarkan nilai hash dari objek. */
    public function cocokkanPassword(string $password): bool
    {
        return Hash::check($password, $this->password_hash);
    }

    /** Mengembalikan data profil publik: idAkun, username, namaTampilan, peran. Dipakai oleh HalamanUtama dan FeedbackController. */
    public function getProfil(): array
    {
        return [
            'id_akun'       => $this->id_akun,
            'username'      => $this->username,
            'nama_tampilan' => $this->nama_tampilan,
            'peran'         => $this->peran,
        ];
    }

    /** Mengembalikan profil beserta email untuk halaman akun milik pengguna sendiri. */
    public function getDetailAkun(): array
    {
        return array_merge($this->getProfil(), ['email' => $this->email]);
    }

    /** Memeriksa apakah peran akun boleh menjalankan aksi tertentu. */
    public function verifikasiHakAkses(string $aksi): bool
    {
        /** @var array<string, list<string>> */
        static $hak = [
            'pelajar' => [
                'MEMPELAJARI_MATERI', 'MENGERJAKAN_LATIHAN', 'MELIHAT_PROGRES',
                'MENGIKUTI_KELAS', 'KIRIM_FEEDBACK',
            ],
            'pengajar' => [
                'KELOLA_KELAS', 'BUAT_KELAS', 'BUAT_TUGAS', 'TERBITKAN_TUGAS',
                'GANTI_KODE', 'KELOLA_ANGGOTA', 'LIHAT_ANGGOTA', 'ATUR_SCOREBOARD',
                'PANTAU_PROGRES', 'KIRIM_FEEDBACK',
            ],
            'tim_materi' => [
                'KELOLA_MATERI',
            ],
        ];

        if ($this->id_akun === null) {
            return false;
        }

        $peranKey = strtolower((string) $this->peran);
        if (! isset($hak[$peranKey])) {
            return false;
        }

        $kode = $this->normalisasiAksi($aksi);

        return in_array($kode, $hak[$peranKey], true);
    }

    /** Mengembalikan nama tampilan akun (DPPL2 §5.1 — ambilNamaTampilan).*/
    public function ambilNamaTampilan(): string
    {
        return (string) $this->nama_tampilan;
    }

    // -------------------------------------------------------------------------
    // public helpers
    // -------------------------------------------------------------------------

    /** Memeriksa apakah akun memiliki salah satu peran yang diberikan. */
    public function memilikiPeran(Peran|string ...$peran): bool
    {
        $daftar = array_map(fn ($p) => $p instanceof Peran ? $p->value : strtolower($p), $peran);

        return in_array(strtolower((string) $this->peran), $daftar, true);
    }

    /** Relasi ke MotivasiBelajar — dipakai modul progres (Natan). */
    public function motivasi(): HasOne
    {
        return $this->hasOne(MotivasiBelajar::class, 'id_pelajar', 'id_akun');
    }

    /** Huruf awal nama tampilan, dipakai untuk avatar. */
    public function inisial(): string
    {
        return mb_strtoupper(mb_substr((string) $this->nama_tampilan, 0, 1));
    }

    // -------------------------------------------------------------------------
    // Private helpers


    /** Normalisasi nama aksi ke SCREAMING_SNAKE_CASE. Contoh: "kelolaMateri" -> "KELOLA_MATERI", "kelola_materi" -> "KELOLA_MATERI" */
    private function normalisasiAksi(string $aksi): string
    {
        // sisipkan "_" sebelum setiap huruf besar yang didahului huruf kecil (camelCase -> snake_case)
        $snake = preg_replace('/([a-z])([A-Z])/', '$1_$2', $aksi) ?? $aksi;

        return strtoupper($snake);
    }
}
