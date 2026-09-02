<h1>
IF2150 REKAYASA PERANGKAT LUNAK
<br>
TUGAS 1
<br>
TOPIC BRAINSTORMING
</h1>
<br>

## *Nama Perangkat Lunak*

### Untuk: Amanda Aurellia Salsabilla

Dipersiapkan oleh:
| Informasi | Keterangan |
| --- | --- |
| Kelas | K2 |
| Kelompok | 4  |

| NIM | Nama |
|---|---|
| 13525029 | Muhammad Naufal Hilmi |
| 13525077 | Muhammad Abduh |
| 13525107 | Nathaniel Marvelo |
| 13525113 | Diandra Aria Yufana |
| 13525143 | Natan Danuarta Ariel Wicaksana |
---

<br>
<br>

# BAB 1: Analisis Permasalahan

## 1.1 Latar Belakang Masalah
Indonesia sebagai salah satu negara multikultural terbesar di dunia memiliki kurang lebih 1340 suku bangsa. Keberagaman yang dimiliki bangsa Indonesia ini menghasilkan berbagai kekayaan budaya, termasuk kurang lebih 700 bahasa daerah yang beberapa diantaranya memiliki sistem penulisan aksara yang unik. Bahasa-bahasa ini bukan hanya menjadi alat komunikasi sehari-hari, tetapi juga memuat nilai-nilai adat istiadat dari tiap daerah.

## 1.2 Analisis Kondisi Saat Ini
Bahasa daerah yang merupakan kekayaan bangsa ini sekarang tengah mengalami tantangan. Adanya ancaman, baik dari dalam maupun luar bangsa menjadikan penggunaan bahasa daerah semakin ditinggalkan. Padahal, bahasa daerah yang merupakan kekayaan bangsa seharusnya dilestarikan.\
Menurut data dari Badan Pengembangan dan Pembinaan Bahasa Kementerian Pendidikan dan Kebudayaan, terdapat 11 bahasa daerah yang telah punah, dan puluhan lainnya terancam punah. Kondisi ini mencerminkan menurunnya partisipasi masyarakat dalam pelestarian bahasa daerah. Terancamnya keberlangsungan bahasa daerah ini disebabkan oleh beberapa faktor, seperti rendahnya kesadaran generasi muda untuk mempelajari dan melestarikan bahasa daerah, pengaruh globalisasi, dan kurangnya pewarisan antargenerasi.\
Rendahnya keinginan generasi muda untuk mempelajari bahasa daerah disebabkan oleh beberapa hal. Salah satu penyebabnya adalah adanya anggapan bahwa bahasa daerah sudah ketinggalan zaman. Munculnya stigma tersebut didukung oleh globalisasi yang menjadikan bahasa asing lebih diminati oleh generasi muda. Selain itu, kurangnya niat generasi muda untuk mempelajari bahasa daerah juga disebabkan oleh metode pembelajaran bahasa daerah di sekolah yang terlalu kaku dan monoton. Hal ini menyebabkan minat atau ketertarikan untuk mempelajari dan melestarikan bahasa daerah berkurang.




---

# BAB 2: Analisis Solusi

## 2.1 Deskripsi Perangkat Lunak
Ngaksara merupakan sebuah media yang menawarkan fitur-fitur untuk menunjang pembelajaran bahasa baru, terutama bahasa dengan aksara yang rumit. Salah satu fitur yang terdapat dalam Ngaksara adalah fitur menggambar suatu karakter sesuai outline dan tanpa outline. Hasil gambar pengguna kemudian akan dinilai keakuratannya dengan karakter asli. Selain itu, fitur mencocokan aksara dengan pelanturan serta fitur menulis pelanturan karakter merupakan solusi kami untuk  meningkatkan familiaritas dan pemahaman akan pelanturan karakter. Ngaksara juga memfasilitasi pembelajaran berbagai aksara, sesuai dengan kebutuhan pengguna dengan opsi untuk menambahkan sendiri karakter yang ingin dipelajari.

## 2.2 Asumsi dan Batasan
Dari segi teknis, pengembangan algoritma penilai kesesuaian gambar dan pattern matching merupakan salah satu aspek yang akan menjadi konundrum bagi kami. Selain itu, aspek pengelolaan dan pengembangan lebih lanjut masih dalam bentuk konsiderasi dan belum kami tentukan.
---

# BAB 3: Spesifikasi Kebutuhan dan Proses Bisnis

## 3.1 Identifikasi Aktor

| Aktor | Deskripsi |
| :--- | :--- |
| Pelajar | Pengguna yang login ke aplikasi untuk belajar dan berlatih aksara Jawa dan Sunda. Pengguna ini dapat menggunakan fitur komunikasi untuk bertanya langsung kepada pengajar jika mengalami kesulitan dalam menjalankan aplikasi maupun kebingungan terkait materi.|
| Pengajar | Pengguna yang login sebagai fasilitator pembelajaran. Pengguna ini memiliki akses untuk melihat rekam jejak latihan pelajar, menganalisis kelemahan yang mereka hadapi berdasarkan data latihan, serta membalas pertanyaan yang diajukan oleh pelajar.|
| Tim Materi | Pengguna yang bertindak sebagai pengelola konten materi. Tim materi membutuhkan akses untuk mengelola modul. |
| Programmer | Pengguna yang bertindak sebagai teknisi pemelihara sistem aplikasi. Pengguna ini memantau kelancaran fitur fitur agar aplikasi berjalan lancar. |


## 3.2 Kebutuhan Pengguna Awal
| ID | Aktor | Kebutuhan / Aktivitas | Tujuan / Nilai |
| :--- | :--- | :--- | :--- |
| US-01 | Pelajar | Melihat bentuk dasar dan panduan baca aksara Jawa dan Sunda | Dapat mengenali bentuk dasar setiap huruf dari kedua aksara tersebut dengan benar. |
| US-02 | Pelajar | Melatih penulisan aksara dengan menebalkan garis. | Terbiasa dengan cara penulisan yang tepat |
| US-03 | Pelajar | Mengirimkan pertanyaan atau pesan kepada pengajar melalui aplikasi | Mendapatkan bantuan langsung dari pengajar saat kesulitan memahami materi |
| US-04 | Pengajar | Melihat rekam jejak dari riwayat penyelesaian latihan masing masing pelajar | Mengetahui perkembangan belajar pelajar secara berkala |
| US-05 | Pengajar | Menganalisis kelemahan penulisan pelajar | Dapat memberikan evaluasi dan bimbingan yang tepat sesuai kekurangan pelajar |
| US-06 | Pengajar | Membaca dan membalas pertanyaan yang dikirimkan oleh pelajar di dalam sistem | Memberikan solusi dan menjaga komunikasi interaktif selama proses pembelajaran |
| US-07 | Tim Materi | Mengunggah modul kurikulum untuk aksara Jawa dan Sunda | Memastikan materi yang disajikan selalu relevan dan lengkap |



## 3.3 Model Proses Bisnis
Buatlah *Activity Diagram* atau *Swimlane Diagram* yang menunjukkan alur kerja proses bisnis dari sistem solusi. Diagram ini harus memvisualisasikan bagaimana alur operasional di dunia nyata berjalan lebih efisien dengan adanya interaksi antara aktor (yang didefinisikan pada poin 3.1) dan sistem perangkat lunak. Perhatikan notasi yang digunakan dalam pembuatannya.
<br>

<p align="center">
<img alt="Contoh Activity Diagram" src="./assets/diagram/diagram-act-1.avif" width="70%">
</p>
<p align="center">
<i>Gambar 1. Contoh Activity Diagram</i>
</p>

<br>

# Referensi
- Diagram UML: https://www.drawio.com/, https://staruml.io/
