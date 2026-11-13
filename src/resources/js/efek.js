// Efek visual Ngaksara (lihat resources/css/efek.css); tanpa gerak bila pengguna memilih prefers-reduced-motion.
const tanpaGerak = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
const formatAngka = new Intl.NumberFormat('id-ID', { maximumFractionDigits: 2 });

// Kartu muncul bertahap (maksimal 8 tingkat agar halaman panjang tidak lambat).
function munculBertahap() {
    document.querySelectorAll('[data-efek-muncul], .nk-konten .kartu').forEach((el, i) => {
        el.style.setProperty('--nk-tunda', `${Math.min(i, 8) * 60}ms`);
    });
    document.documentElement.classList.add('nk-efek-siap');
}

// Bilah progres mulai dari 0 lalu terisi ke lebar aslinya.
function isiBilah() {
    const bilah = [...document.querySelectorAll('.bilah > span')];
    const target = bilah.map((el) => el.style.width);
    bilah.forEach((el) => { el.style.width = '0%'; });
    requestAnimationFrame(() => requestAnimationFrame(() => bilah.forEach((el, i) => { el.style.width = target[i]; })));
}

// Angka naik dari 0 ke nilai akhir; teks lain di sekitar angka dipertahankan.
function hitungNaik(el, nilai, durasi = 900) {
    const mulai = performance.now();
    const langkah = (sekarang) => {
        const t = Math.min(1, (sekarang - mulai) / durasi);
        el.textContent = formatAngka.format(nilai * (1 - Math.pow(1 - t, 3)));
        if (t < 1) requestAnimationFrame(langkah);
    };
    requestAnimationFrame(langkah);
}

// Taburan motif kawung warna Ngaksara dari atas layar.
function taburKawung(jumlah = 28) {
    const wadah = document.createElement('div');
    wadah.className = 'nk-taburan';
    wadah.setAttribute('aria-hidden', 'true');
    const warna = ['var(--nk-emas)', 'var(--nk-indigo)', 'var(--nk-sogan)', 'var(--nk-teal)'];
    for (let i = 0; i < jumlah; i++) {
        const k = document.createElement('span');
        k.className = 'nk-kawung';
        k.style.left = `${Math.random() * 100}%`;
        k.style.setProperty('--nk-warna', warna[i % warna.length]);
        k.style.setProperty('--nk-tunda', `${Math.random() * 0.6}s`);
        k.style.setProperty('--nk-durasi', `${2 + Math.random() * 1.4}s`);
        k.style.setProperty('--nk-geser', `${(Math.random() - 0.5) * 160}px`);
        k.style.setProperty('--nk-putar', `${(Math.random() - 0.5) * 540}deg`);
        wadah.appendChild(k);
    }
    document.body.appendChild(wadah);
    setTimeout(() => wadah.remove(), 4200);
}

function toastBadge(nama) {
    const toast = document.createElement('div');
    toast.className = 'nk-toast';
    toast.setAttribute('role', 'status');
    toast.innerHTML = '<img src="/img/ikon-gunungan.svg" alt=""><span></span>';
    toast.querySelector('span').textContent = `Badge baru: ${nama}`;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 4400);
}

// I-10 Hasil latihan: skor naik, kawung bila berhasil, maskot bergoyang bila perlu diulang, badge pop + toast.
function efekHasil() {
    const hasil = document.querySelector('[data-efek-hasil]');
    if (!hasil) return;
    const skor = hasil.querySelector('[data-efek-skor]');
    if (skor) hitungNaik(skor, parseFloat(hasil.dataset.skor) || 0);
    hasil.querySelectorAll('[data-efek-denyut]').forEach((el) => el.classList.add('nk-denyut'));
    if (hasil.dataset.lulus === '1') {
        setTimeout(() => taburKawung(), 350);
    } else {
        hasil.querySelector('img')?.classList.add('nk-goyang');
    }
    hasil.querySelectorAll('[data-efek-badge]').forEach((el, i) => {
        el.style.setProperty('--nk-tunda', `${0.7 + i * 0.2}s`);
        el.classList.add('nk-pop');
        setTimeout(() => toastBadge(el.dataset.efekBadge), 900 + i * 600);
    });
}

// I-13 Tugas baru dikumpulkan: tanda centang tergambar di samping pesan status.
function efekKumpul() {
    const pesan = document.querySelector('[data-efek-kumpul]');
    if (!pesan) return;
    const warna = pesan.dataset.efekKumpul === 'tepat_waktu' ? 'var(--nk-hijau)' : 'var(--nk-emas)';
    pesan.insertAdjacentHTML('afterbegin', `<svg class="nk-centang-gambar" viewBox="0 0 44 44" aria-hidden="true">
        <circle cx="22" cy="22" r="20" fill="none" stroke="${warna}" stroke-width="3"/>
        <path d="M13 22.5l6 6 12-13" fill="none" stroke="${warna}" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"/></svg>`);
    if (pesan.dataset.efekKumpul === 'tepat_waktu') setTimeout(() => taburKawung(18), 500);
}

document.addEventListener('DOMContentLoaded', () => {
    if (tanpaGerak) return;
    munculBertahap();
    isiBilah();
    efekHasil();
    efekKumpul();
});
