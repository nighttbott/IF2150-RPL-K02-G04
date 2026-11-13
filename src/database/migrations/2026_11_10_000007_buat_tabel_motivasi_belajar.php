<?php

use App\Support\BatasanCheck;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Tabel motivasi_belajar (C22 MotivasiBelajar, pemilik modul: Natan). Satu baris per Pelajar. Badge disimpan sebagai daftar JSON; NULL dibaca sebagai daftar kosong. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('motivasi_belajar', function (Blueprint $table) {
            $table->unsignedBigInteger('id_pelajar')->primary();
            $table->unsignedInteger('streak')->default(0);
            $table->unsignedInteger('poin')->default(0);
            $table->date('tanggal_aktif_terakhir')->nullable();
            $table->json('daftar_badge')->nullable();
            $table->foreign('id_pelajar')->references('id_akun')->on('akun_pengguna')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('motivasi_belajar');
    }
};
