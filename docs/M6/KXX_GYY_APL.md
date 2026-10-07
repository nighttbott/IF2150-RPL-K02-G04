<h1>
IF2150 REKAYASA PERANGKAT LUNAK
<br>
TUGAS 6
<br>
ARSITEKTUR PERANGKAT LUNAK (APL)
</h1>
<br>

## Ngaksara

### Untuk: Amanda Aurellia Salsabilla

Dipersiapkan oleh:
| Informasi | Keterangan |
| --- | --- |
| Kelas | K2 |
| Kelompok | 4 |

| NIM      | Nama                           |
| -------- | ------------------------------ |
| 13525026 | Ryuza Nadif Aldebaran          |
| 13525029 | Muhammad Naufal Hilmi          |
| 13525077 | Muhammad Abduh                 |
| 13525107 | Nathaniel Marvelo              |
| 13525113 | Diandra Aria Yufana            |
| 13525143 | Natan Danuarta Ariel Wicaksana |

---

<br>
<br>

# BAB 1: Style/Pattern Arsitektur Acuan

## 1.1 Sytle/Pattern yang Dipilih

Ngaksara menggunakan arsitektur client-server dengan pola Model-View-Controller (MVC). Pengguna mengakses aplikasi melalui browser sebagai client, sedangkang sever mengolah permintaan dan menyimpan data. Bagian server dibangun sebagai satu aplikasi Laravel dengan pembagian tugas sebagai berikut :

- Model mengelola data dan aturan aplikasi, seperti akun pengguna, materi, hasil latihan, dan kelas.
- View menampilkan halaman yang digunakan Pelajar, Pengajar, dan Tim Materi.
- Controller menerima permintaan dari pengguna, memprosesnya dengan bantuan Model, lalu menyiapkan hasil untuk ditampilkan melalui View.

Data aplikasi disimpan dalam MySQL, sedangkan berkas materi, gambar, audio, dan template aksara disimpan pada server.

## 1.2 Alasan Pemilihan

Kami memilih client-server karena Ngaksra digunakan oleh tiga jenis pengguna yang mengakses data yang saling berkaitan. Misalnya, hasil latihan Pelajar perlu disimpan agar dapat ditampilkan pada halaman progres dan dipantau oleh Pengajar sesuai hak aksesnya. Pengelolaan data di server mendukung kebutuhan tersebut, termasuk pembatasan akses pengguna pada KF02 dan pencatatan riwayat pada KF14.

Pola MVC dipilih agar tampilan, pengolahan permintaan, dan pengelolaan data memiliki tanggung jawab yang jelas. Pembagian ini memudahkan anggota kelompok mengerjakan bagian aplikasi dan melakukan perbaikan. Contohnya, perubahan tampilan latihan tidak perlu mengubah cara penyimpanan riwayat belajar.

Untuk memenuhi kebutuhan respons menggambar pada KNF04, goresan langsung ditampilkan melalui Canvas di browser tanpa menunggu server. Server tetap menangani penilaian akhir dan penyimpanan hasil. Pemeriksaan hak ases serta penyimpanan kata sandi dalam bentuk hash juga dilakukan di server untuk mendukung KF05 dan KNF02. Rancangan ini cukup sederhana untuk demonstrasi melalui localhost atau LAN, meskipun pengguna tetap membutuhkan koneksi ke server untuk menyimpan data.

## 1.3 Diagram Penerapan Arsitektur
Diagram berikut menunjukkan pembagian komponen Ngaksara berdasarkan pola MVC serta hubungannya dengan penyimpanan dan pencatatan log.

<p align="center">
<img alt="Contoh Arsitektur MVC" src="./assets/diagram/contoh-arsitektur-mvc.webp" width="70%">
</p>
<p align="center">
<i>Gambar 1. Contoh Arsitektur MVC</i>
</p>

Template View dibuat menggunakan Blade di server, lalu hasilnya ditampilkan di browser. Permintaan pengguna dikirim ke Controller, sedangkan Model digunakan untuk mengelola data yang dibutuhkan.

## 1.4 Lingkungan Operasi Perangkat Lunak

Tabel 1.1. Lingkungan Operasi Perangkat Lunak

| Komponen | Spesifikasi |
| :--- | :--- |
| Server aplikasi | Laravel 13 dengan PHP 8.4. |
| OS lingkungan demonstrasi | Windows 11 64-bit dan Linux|
| Cara menjalankan demonstrasi | Server pengembangan Laravel pada localhost atau jaringan lokal. |
| Rencana OS jika dipublikasikan | Ubuntu Server 24.04 LTS dengan Nginx dan PHP-FPM 8.4.|
| Basis data | MySQL 8.4 dengan `utf8mb4`. |
| Antarmuka | Blade, HTML, CSS, Bootstrap 5.3, dan JavaScript. Aset disimpan bersama aplikasi. |
| Interaksi menggambar | Canvas API dan Pointer Events. |
| Penyimpanan | Laravel Filesystem pada disk lokal untuk materi, audio, gambar, dan data template. |
| Alokasi awal server | Dua inti CPU, RAM 4 GB untuk proses server dan ruang kosong 10 GB untuk aplikasi serta data awal.|
| Client | Chrome, Edge, atau Firefox versi stabil yang tersedia.|
| Perangkat pengguna | Mouse atau layar sentuh, browser dengan JavaScript aktif, serta speaker/headphone untuk latihan audio. |
| Jaringan demonstrasi | Localhost atau LAN. Koneksi internet tidak menjadi ketergantungan runtime apabila seluruh aset dan server tersedia secara lokal. |
| Integrasi eksternal | Tidak digunakan pada implementasi awal. |
| Pemeliharaan | Pengembang memeriksa log aplikasi dan kondisi server menggunakan sarana operasional. Tidak ditambahkan aktor Administrator maupun antarmuka pemeliharaan baru. |

---

# BAB 2: Identifikasi Komponen / Modul / Subsistem

Tabel 2.1. Identifikasi Komponen/Modul/Subsistem

| Nama Komponen/Modul/Subsistem | Jenis | Penjelasan |
| :--- | :--- | :--- |
| `HalamanPendaftaran` | View | Menampilkan form pendaftaran, pemilihan peran pengguna, dan persetujuan Terms & Conditions. |
| `HalamanLogin` | View | Menampilkan form untuk memasukkan kredensial login. |
| `HalamanUtama` | View | Menampilkan navigasi utama setelah pengguna berhasil masuk ke sistem. |
| `HalamanMateri` | View | Menampilkan antarmuka bagi Pelajar untuk memilih dan mempelajari materi, termasuk memutar audio pengucapan. |
| `HalamanLatihan` | View | Menampilkan antarmuka latihan menulis, mencocokkan bunyi, merangkai aksara, dan transliterasi. |
| `HalamanProgres` | View | Menampilkan riwayat, progres, rekomendasi materi, dan indikator motivasi belajar Pelajar. |
| `HalamanKelasPelajar` | View | Menampilkan antarmuka bagi Pelajar untuk bergabung ke kelas, melihat tugas, dan mengumpulkan tugas. |
| `HalamanKelolaKelas` | View | Menampilkan antarmuka bagi Pengajar untuk mengelola kelas dan memantau anggotanya. |
| `HalamanKelolaMateri` | View | Menampilkan antarmuka bagi Tim Materi untuk mengunggah dan menyunting materi. |
| `HalamanFeedback` | View | Menampilkan form untuk mengirim feedback aplikasi, materi, atau pengalaman penggunaan. |
| `OtentikasiController` | Controller | Memvalidasi pendaftaran dan login, serta memproses autentikasi pengguna. |
| `MateriController` | Controller | Mengambil, mengurutkan, memuat, dan memperbarui data materi. |
| `LatihanController` | Controller | Menyiapkan soal latihan, menilai jawaban, serta memperbarui riwayat dan motivasi belajar. |
| `ProgresController` | Controller | Mengolah riwayat dan progres belajar, menyiapkan rekomendasi materi serta papan peringkat. |
| `KelasController` | Controller | Memproses keanggotaan kelas, data kelas, tugas, dan pengambilan data kelas. |
| `FeedbackController` | Controller | Memvalidasi dan menyimpan feedback, serta menyiapkan konfirmasi pengiriman. |
| `AkunPengguna` | Model | Menyimpan kredensial, profil, dan peran Pelajar, Pengajar, atau Tim Materi. |
| `MateriAksara` | Model | Menyimpan metadata materi, jenis aksara, dan tingkat kesulitan. |
| `KontenMateri` | Model | Menyimpan isi materi, contoh, aturan, audio, dan versi konten. |
| `RiwayatLatihan` | Model | Menyimpan hasil latihan, status, dan waktu pengerjaan Pelajar. |
| `MotivasiBelajar` | Model | Menyimpan streak, poin, dan badge Pelajar untuk motivasi dan papan peringkat. |
| `KelasBelajar` | Model | Menyimpan data kelas, pemilik kelas, kode bergabung, dan pengaturan kelas. |
| `KeanggotaanKelas` | Model | Menyimpan hubungan Pelajar dengan kelas dan status keanggotaannya. |
| `TugasKelas` | Model | Menyimpan instruksi, komponen, dan tenggat tugas. |
| `PenyelesaianTugas` | Model | Menyimpan status dan waktu pengumpulan tugas oleh Pelajar. |
| `DataFeedback` | Model | Menyimpan feedback, pengirim, waktu kirim, dan status tindak lanjut. |
| `Database` | Penyimpanan Data | Menyimpan seluruh data Model secara persisten pada MySQL 8.4 yang digunakan aplikasi. |

---

# BAB 3: Model Arsitektur Perangkat Lunak

*Architectural View* adalah bagaimana cara kita melihat/mendeskripsikan arsitektur sebuah sistem dari sudut pandang tertentu. Dalam perancangan arsitektur aplikasi, dibutuhkan *Architectural View* yang dapat mempermudah pemahaman dari proses aplikasi yang akan dikembangkan. Tujuan dari *Architectural View* adalah menjadi bahan komunikasi, pemisahan masalah, mempermudah analisis, dan pemandu saat eksekusi pengembangan sistem tersebut.

Buatlah model arsitektur dari aplikasi yang akan dirancang dalam bentuk *view*. Model arsitektur ini berfungsi untuk memperlihatkan bagaimana setiap komponen, modul, dan subsistem saling berinteraksi serta berkolaborasi dalam menjalankan fungsi utama sistem secara keseluruhan. Anda dapat membuat satu atau lebih *view* tergantung kebutuhan dalam bentuk gambar. Pilihlah notasi yang sesuai. Contoh *view* yang dapat digunakan antara lain ***Logical View***, ***Process View***, ***Development View***, serta ***Physical View***.

Ketentuan pengisian BAB 3:
1. Setiap view menggambarkan **keseluruhan sistem**, bukan satu use case atau satu fitur saja.
2. Buat **minimal satu view**. Setiap view dituliskan dalam subbab tersendiri (3.1, 3.2, dan seterusnya). Tidak perlu membuat keempat view, pilih yang paling membantu menjelaskan P/L Anda, lalu jelaskan alasan pemilihannya.
3. Setiap view harus **konsisten dengan BAB 2**. Seluruh komponen pada Tabel 2.1 harus muncul dengan nama yang sama, dan tidak boleh ada komponen pada view yang tidak terdaftar di Tabel 2.1.
4. Setiap view harus **mencerminkan style/pattern pada BAB 1**. Misalnya, jika memilih MVC, pembagian *Model*, *View*, dan *Controller* harus terlihat jelas pada diagram.
5. Jika membuat lebih dari satu view, setiap view harus menggambarkan sistem yang sama dari sudut pandang berbeda. View tambahan melengkapi view pertama, bukan mengulanginya.
6. Beri label pada setiap garis atau panah yang menghubungkan komponen agar hubungan antarkomponen dapat dipahami tanpa penjelasan tambahan.
7. Jika membuat *Physical View*, gambarkan lingkungan operasi pada Tabel 1.1.

## 3.1 XXX View

Tuliskan secara singkat mengenai model arsitektur perangkat lunak yang Anda pilih dan sertakan alasan mengapa model arsitektur tersebut cocok untuk aplikasi Anda.

<p align="center">
<img alt="Contoh Logical View pada P/L E-Commerce" src="./assets/diagram/DiagramViewFinal.webp" width="100%">
</p>
<p align="center">
<i>Gambar 2. Contoh Logical View pada P/L E-Commerce</i>
</p>

Gambar 2 adalah contoh *Logical View* dalam bentuk *block diagram*. Seluruh komponen pada Tabel 2.1 digambarkan dan dikelompokkan sesuai pola MVC (*View*, *Controller*, *Model*), ditambah komponen pendukung dan basis data. Sistem di luar P/L, seperti *Payment Gateway (dummy)*, digambarkan dengan garis putus-putus dan tidak perlu dimasukkan ke Tabel 2.1. Setiap garis diberi label: "Memanggil" untuk *View* yang memanggil *Controller*, "akses" untuk *Controller* yang mengakses *Model*, serta agregasi dan komposisi untuk hubungan antar-*Model*.

<sub><b><i>Catatan</i></b>: <i>Ganti XXX dengan nama view yang dibuat, misalnya Logical View. Gambar 2 hanya contoh untuk P/L e-commerce, ganti dengan view milik kelompok Anda yang memuat seluruh komponen pada Tabel 2.1. Jenis view dan notasinya boleh berbeda dari contoh. Jika membuat view tambahan, lanjutkan pola 3.x ini (3.2, 3.3, dan seterusnya).</i></sub>

---

# Referensi

- Sommerville, I. (2016). *Software Engineering* (10th ed.). Pearson. Chapter 6: *Architectural Design*: [https://software-engineering-book.com/slides/](https://software-engineering-book.com/slides/)
- Diagram arsitektur: [https://www.drawio.com/](https://www.drawio.com/), [https://staruml.io/](https://staruml.io/)
