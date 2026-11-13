# Ngaksara

Aplikasi web pembelajaran aksara Jawa dan Sunda (IF2150 Rekayasa Perangkat Lunak, K02-G04, kelompok 0sks).
Pelajar mempelajari materi, berlatih menulis di kanvas, mencocokkan bunyi, merangkai, dan transliterasi, lalu
memantau progres, streak, poin, dan badge. Pengajar membuat kelas, menerbitkan tugas, dan memantau anggota.
Tim Materi menambah, menyunting, menerbitkan, dan mengarsipkan materi. Arsitektur: MVC Laravel (DPPL2 bab 2).

## Teknologi

| Komponen | Versi |
|---|---|
| PHP | 8.3 atau lebih baru (diuji dengan 8.4; DPPL: 8.4) |
| Laravel | 13.x (terkunci di `composer.lock`, platform PHP 8.3) |
| MySQL | 8.4 melalui Docker Compose |
| Tampilan | Blade + Bootstrap 5.3, dibangun dengan Vite |
| Node.js | 20.19 atau lebih baru |
| Pengujian | PHPUnit 12 (SQLite di memori) |

## Prasyarat

- PHP 8.3+ dengan ekstensi `pdo_mysql`, `pdo_sqlite`, `mbstring`, `xml`, `fileinfo`, `openssl`
- Composer 2, Node.js 20.19+ dan npm
- Docker Desktop (untuk MySQL)

## Instalasi dari clone bersih

Jalankan dari folder akar repositori (tempat `docker-compose.yml` berada):

```bash
docker compose up -d            # menyalakan MySQL 8.4
cd src
composer install
cp .env.example .env            # PowerShell: Copy-Item .env.example .env
php artisan key:generate
php artisan migrate --seed      # membuat tabel dan data uji
npm install
npm run build                   # atau: npm run dev selama pengembangan
php artisan serve               # buka http://localhost:8000
```

Nilai `DB_*` di `.env.example` sudah sama dengan `docker-compose.yml`. Jangan commit berkas `.env`.

## Akun uji (fiktif)

Semua akun memakai kata sandi `Rahasia123!`.

| Peran | Email | Keterangan |
|---|---|---|
| Pelajar | `pelajar@ngaksara.test` | Sari, anggota aktif kelas contoh |
| Pelajar | `budi@ngaksara.test`, `wulan@ngaksara.test` | anggota aktif |
| Pelajar | `raka@ngaksara.test` | sudah **keluar** dari kelas (untuk uji pemantauan) |
| Pengajar | `pengajar@ngaksara.test` | Bu Rina, pemilik kelas "Aksara Jawa Dasar" (kode `K7M2QX`) |
| Pengajar | `pengajar2@ngaksara.test` | Pak Dimas, bukan pemilik kelas (uji hak akses) |
| Tim Materi | `materi@ngaksara.test` | Mas Bayu |

Akun baru juga bisa dibuat lewat halaman **Daftar** (`/daftar`) untuk peran Pelajar atau Pengajar.
Akun Tim Materi hanya tersedia dari seeder.

## Alamat penting

| Halaman | URL | Peran |
|---|---|---|
| Masuk / Daftar | `/masuk`, `/daftar` | tamu |
| Beranda | `/` | semua (Pengajar dan Tim Materi dialihkan ke menu pertamanya) |
| Materi, Latihan, Progres, Kelas | `/materi`, `/latihan`, `/progres`, `/pelajar/kelas` | Pelajar |
| Kelola Kelas, Pantau Progres | `/pengajar/kelas`, `/pemantauan` | Pengajar |
| Kelola / Unggah Materi | `/kelola/materi`, `/kelola/materi/baru` | Tim Materi |
| Feedback | `/feedback` | Pelajar dan Pengajar |

## Mengatasi masalah umum

| Gejala | Penyebab dan solusi |
|---|---|
| `Unable to locate file in Vite manifest` | Aset belum dibangun. Jalankan `npm install` lalu `npm run build`. |
| `SQLSTATE[HY000] [2002]` saat migrasi | MySQL belum siap. Tunggu beberapa detik setelah `docker compose up -d`, lalu ulangi. |
| Port 3306 sudah dipakai | Matikan MySQL lokal lain, atau ubah port di `docker-compose.yml` dan `DB_PORT` di `.env`. |
| Ingin data awal lagi | `php artisan migrate:fresh --seed` (menghapus seluruh isi basis data lokal). |

## Konvensi nilai di basis data

Semua nilai tetap memakai **huruf kecil** dan dikunci dengan batasan `CHECK` pada MySQL.
Gunakan enum di `app/Enums` agar tidak salah tulis.

| Kolom | Nilai |
|---|---|
| `akun_pengguna.peran` | `pelajar`, `pengajar`, `tim_materi` |
| `materi_aksara.jenis_aksara` | `jawa`, `sunda` |
| `materi_aksara.status_publikasi` | `draf`, `terbit`, `arsip` |
| `kelas_belajar.status` | `aktif`, `nonaktif` |
| `keanggotaan_kelas.status` | `aktif`, `keluar`, `nonaktif` |
| `tugas_kelas.status` | `draf`, `terbit` |
| `komponen_tugas.jenis` | `materi`, `latihan` |
| `riwayat_latihan.mode`, `komponen_tugas.mode` | `menulis`, `bunyi`, `merangkai`, `transliterasi` |
| `riwayat_latihan.status` | `berhasil`, `perlu_diulang`, `selesai` |
| `riwayat_latihan.mode_aktivitas` | `latihan`, `materi`, `tugas`, `pengumpulan` |
| `penyelesaian_tugas.status` | `tepat_waktu`, `terlambat` |
| `data_feedback.kategori` | `performa`, `tampilan`, `fitur`, `materi` |
| `data_feedback.status` | `baru`, `ditinjau`, `selesai` |

## Modul dan pembagian kerja

| Modul (UC) | Pemilik | Berkas route |
|---|---|---|
| Akun, autentikasi, layout per peran, Beranda (UC-01), Feedback (UC-09) | Ryuza Nadif Aldebaran (13525026) | `routes/auth.php`, `routes/web.php`, `routes/feedback.php` |
| Latihan menulis (kanvas) dan penilaian (UC-03) | Muhammad Naufal Hilmi (13525029) | `routes/latihan.php` |
| Kelas Pelajar, tugas, pengumpulan (UC-05) | Muhammad Abduh (13525077) | `routes/kelas-pelajar.php` |
| Kelola kelas (UC-07), latihan bunyi/merangkai/transliterasi (UC-03) | Nathaniel Marvelo (13525107) | `routes/kelas-pengajar.php`, `routes/latihan.php` |
| Materi, kelola materi, templat aksara (UC-02, UC-06) | Diandra Aria Yufana (13525113) | `routes/materi.php` |
| Basis data, riwayat, motivasi, progres, pemantauan (UC-04, UC-08) | Natan Danuarta Ariel Wicaksana (13525143) | `routes/progres.php` |

## Daftar tabel

Migrasi ada di `database/migrations`. PK = kunci utama, FK = kunci asing.

| Tabel | Atribut |
|---|---|
| `akun_pengguna` | `id_akun` (PK), `email` (unik), `username` (unik), `password_hash`, `peran`, `nama_tampilan`, `created_at`, `updated_at` |
| `materi_aksara` | `id_materi` (PK), `nama_materi`, `jenis_aksara`, `tingkat_kesulitan`, `status_publikasi`, `created_at`, `updated_at` |
| `konten_materi` | `id_konten` (PK), `id_materi` (FK, unik), `bentuk_aksara`, `aturan`, `contoh`, `path_audio`, `versi_konten`, `versi_aplikasi_minimum`, `created_at`, `updated_at` |
| `konten_materi_arsip` | `id_arsip` (PK), `id_konten`, `id_materi` (FK), `bentuk_aksara`, `aturan`, `contoh`, `path_audio`, `versi_konten`, `versi_aplikasi_minimum`, `diarsipkan_pada` |
| `kelas_belajar` | `id_kelas` (PK), `nama`, `pemilik_id` (FK akun), `kode_bergabung` (unik), `status`, `status_scoreboard` |
| `keanggotaan_kelas` | `id_keanggotaan` (PK), `id_kelas` (FK), `id_pelajar` (FK), `status`; unik (`id_kelas`, `id_pelajar`) |
| `tugas_kelas` | `id_tugas` (PK), `id_kelas` (FK), `instruksi`, `tenggat`, `status` |
| `komponen_tugas` | `id_komponen` (PK), `id_tugas` (FK), `jenis`, `id_materi` (FK), `mode`, `wajib` |
| `riwayat_latihan` | `id_riwayat` (PK), `id_pelajar` (FK), `id_materi` (FK), `target_soal`, `mode`, `skor`, `tanggal_pengerjaan`, `status`, `id_tugas` (FK), `id_komponen` (FK), `mode_aktivitas`, `waktu_selesai`; indeks `idx_riwayat_agregasi`, `idx_riwayat_pelajar_materi` |
| `motivasi_belajar` | `id_pelajar` (PK, FK akun), `streak`, `poin`, `tanggal_aktif_terakhir`, `daftar_badge` (JSON) |
| `penyelesaian_tugas` | `id_penyelesaian` (PK), `id_tugas` (FK), `id_pelajar` (FK), `waktu_kumpul`, `status`; unik (`id_tugas`, `id_pelajar`) |
| `data_feedback` | `id_feedback` (PK), `id_akun` (FK), `kategori`, `pesan`, `waktu_kirim`, `status` |

Tabel bawaan Laravel: `sessions`, `cache`, `cache_locks`, `jobs`, `job_batches`, `failed_jobs`.

## Pengujian

```bash
php artisan test               # atau: php vendor/bin/phpunit
```

Tes memakai SQLite di memori (lihat `phpunit.xml`), jadi tidak mengubah basis data MySQL.

## Profiling (B.2.1)

```bash
php artisan db:seed --class=ProfilingSeeder                     # 100 Pelajar, 10.000 riwayat
php artisan ngaksara:profil-progres --ulang=10 --periode=semua   # waktu dan jumlah query + EXPLAIN
```

Untuk mengulang pengukuran "sebelum indeks", lepas indeksnya dulu, ukur, lalu pasang lagi dan ukur ulang:

```bash
php artisan migrate:rollback --path=database/migrations/2026_11_13_000001_tambah_indeks_riwayat_latihan.php
php artisan ngaksara:profil-progres --ulang=10 --periode=semua
php artisan migrate
php artisan ngaksara:profil-progres --ulang=10 --periode=semua
```

Hasil pengukuran ada di `docs/M9/K02_G04_Implementasi.md` bagian B.2.1.
