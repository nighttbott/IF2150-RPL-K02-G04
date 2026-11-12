<?php

namespace App\Support;

use App\Enums\Peran;

/**
 * Data yang dikirim dari OtentikasiController ke AkunPengguna::buatAkunBaru().
 *
 * passwordHash sudah di-hash oleh OtentikasiController.hashPassword() sebelum objek ini dibuat.
 * namaTampilan boleh null — bila null, AkunPengguna menggantinya dengan username (Algo-001).
 */
final readonly class DataPendaftaran
{
    public function __construct(
        public string $email,
        public string $username,
        public string $passwordHash,
        public Peran $peran,
        public ?string $namaTampilan = null,
    ) {}
}
