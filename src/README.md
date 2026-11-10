# Ngaksara

Aplikasi web pembelajaran aksara Jawa dan Sunda (IF2150 Rekayasa Perangkat Lunak, K02-G04).

## Teknologi

| Komponen | Versi |
|---|---|
| PHP | 8.3 atau lebih baru (DPPL: 8.4) |
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

Selama halaman login belum selesai, pada `APP_ENV=local` tersedia route sementara
`/dev/masuk/pelajar`, `/dev/masuk/pengajar`, `/dev/masuk/tim_materi`, dan `/dev/keluar`
(berkas `routes/dev.php`). **Hapus berkas tersebut sebelum release.**

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
| Akun dan autentikasi (UC-01), Feedback (UC-09) | Ryuza | `routes/auth.php`, `routes/feedback.php` |
| Materi dan kelola materi (UC-02, UC-06) | Diandra | `routes/materi.php` |
| Latihan (UC-03) | Naufal | `routes/latihan.php` |
| Kelas pelajar dan tugas (UC-05) | Abduh | `routes/kelas-pelajar.php` |
| Kelola kelas (UC-07) | Marvel | `routes/kelas-pengajar.php` |
| Progres dan pemantauan (UC-04, UC-08) | Natan | `routes/progres.php` |

## Daftar tabel

`akun_pengguna`, `materi_aksara`, `konten_materi`, `konten_materi_arsip`, `kelas_belajar`,
`keanggotaan_kelas`, `tugas_kelas`, `komponen_tugas`, `riwayat_latihan`, `motivasi_belajar`,
`penyelesaian_tugas`, `data_feedback`.

## Pengujian

```bash
php artisan test               # atau: php vendor/bin/phpunit
```

## Profiling (B.2.1)

Perintah `ngaksara:profil-progres` tersedia setelah modul progres digabung.

```bash
php artisan db:seed --class=ProfilingSeeder              # 100 Pelajar, 10.000 riwayat
php artisan ngaksara:profil-progres --ulang=10            # ukur sebelum indeks
php artisan migrate --path=database/migrations-profiling
php artisan ngaksara:profil-progres --ulang=10            # ukur sesudah indeks
```
