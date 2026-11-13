<?php

use App\Support\BatasanCheck;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Tabel akun_pengguna (C01 AkunPengguna, pemilik modul: Ryuza; migrasi disiapkan Natan). Kata sandi hanya disimpan sebagai hash. Peran berupa teks huruf kecil. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('akun_pengguna', function (Blueprint $table) {
            $table->id('id_akun');
            $table->string('email', 255)->unique();
            $table->string('username', 50)->unique();
            $table->string('password_hash', 255);
            $table->string('peran', 12);
            $table->string('nama_tampilan', 100);
            $table->timestamps();
        });

        BatasanCheck::tambah('akun_pengguna', 'chk_akun_peran', BatasanCheck::salahSatu('peran', ['pelajar', 'pengajar', 'tim_materi']));
    }

    public function down(): void
    {
        Schema::dropIfExists('akun_pengguna');
    }
};
