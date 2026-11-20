# Pengujian Use Case HIUPL (Playwright)

`uji_uc.py` menjalankan 27 test case use case (UC01-TC01 s.d. UC09-TC02) serta pengukuran KNF03-TC02 dan KNF04-TC01 di Chromium, terhadap server Laravel yang benar-benar berjalan. Tiap kasus menyimpan `<ID>.png` dan hasilnya ke `hasil_uc.json` di folder keluaran.

## Langkah (PowerShell, dari root repo)

```powershell
pip install playwright
python -m playwright install chromium

cd src
npm.cmd install
npm.cmd run build
# SQLite terpisah supaya data MySQL tidak berubah
New-Item database\e2e.sqlite -ItemType File -Force
$env:DB_CONNECTION="sqlite"; $env:DB_DATABASE="$PWD\database\e2e.sqlite"
php artisan migrate:fresh --seed --force
cd ..
python docs/M10/pengujian/uji_uc.py --siapkan
```

Terminal 1 (biarkan menyala):

```powershell
cd src; $env:DB_CONNECTION="sqlite"; $env:DB_DATABASE="$PWD\database\e2e.sqlite"; php artisan serve
```

Terminal 2 (semua kasus, atau sebut ID tertentu di belakangnya):

```powershell
python docs/M10/pengujian/uji_uc.py <folder-keluaran>
python docs/M10/pengujian/uji_uc.py <folder-keluaran> UC03-TC01 UC03-TC04
```

## Catatan

- Sebelum mengulang semua kasus, jalankan lagi `migrate:fresh --seed` dan `--siapkan`, karena beberapa kasus mengubah data (akun baru, anggota dikeluarkan).
- UC09-TC02 mensimulasikan galat basis data dengan mengganti nama tabel `data_feedback` sementara, lalu mengembalikannya.
- Alamat server dan lokasi basis data bisa diganti lewat `NGAKSARA_URL` dan `NGAKSARA_DB`.
- `contoh-audio.mp3` adalah nada 440 Hz selama 1 detik, dipakai UC02-TC01 dan UC06-TC01.
- Jika `npm` diblokir execution policy PowerShell, pakai `npm.cmd` seperti di atas.
