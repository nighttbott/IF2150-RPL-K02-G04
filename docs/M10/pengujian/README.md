# Pengujian Use Case HIUPL (Playwright)

Skrip `uji_uc.py` menjalankan 27 test case use case (UC01-TC01 s.d. UC09-TC02) dan pengukuran KNF03-TC02 serta KNF04-TC01 di Chromium terhadap server Laravel sungguhan. Setiap kasus menyimpan tangkapan layar `<ID>.png` dan hasilnya ke `hasil_uc.json`.

## Langkah (Windows PowerShell, dari root repo)

```powershell
pip install playwright
python -m playwright install chromium

cd src
npm install
npm run build
# Pakai basis data SQLite terpisah agar data MySQL kamu tidak berubah
New-Item database\e2e.sqlite -ItemType File -Force
$env:DB_CONNECTION="sqlite"; $env:DB_DATABASE="$PWD\database\e2e.sqlite"
php artisan migrate:fresh --seed --force
cd ..
python docs/M10/pengujian/uji_uc.py --siapkan

# Terminal 1 (biarkan menyala)
cd src; $env:DB_CONNECTION="sqlite"; $env:DB_DATABASE="$PWD\database\e2e.sqlite"; php artisan serve

# Terminal 2
python docs/M10/pengujian/uji_uc.py docs/M10/assets/uc
```

Catatan:

- Jalankan ulang `migrate:fresh --seed` dan `--siapkan` sebelum menjalankan semua kasus lagi, karena beberapa kasus mengubah data (misalnya akun baru dan anggota yang dikeluarkan).
- UC09-TC02 mensimulasikan kegagalan basis data dengan mengganti nama tabel `data_feedback` sementara, lalu mengembalikannya.
- Alamat server dan lokasi basis data dapat diganti lewat variabel `NGAKSARA_URL` dan `NGAKSARA_DB`.
- `contoh-audio.mp3` adalah nada 440 Hz selama 1 detik yang dibuat dengan ffmpeg, dipakai untuk UC02-TC01 dan UC06-TC01.
