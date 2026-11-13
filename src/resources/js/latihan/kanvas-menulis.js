// C08 HalamanLatihan, mode MENULIS (DPPL 5.8). Menggambar sepenuhnya di browser; server hanya dipanggil saat Periksa dan Selesaikan (KNF04).

const kanvas = document.getElementById('kanvas');
const ctx = kanvas.getContext('2d');
const data = kanvas.dataset;
const sakelar = document.getElementById('outline');
const bilah = document.getElementById('bilah-hasil');
const tombolPeriksa = document.getElementById('periksa');
const csrf = document.querySelector('meta[name="csrf-token"]').content;
const gaya = getComputedStyle(document.documentElement);

const goresan = [];
let goresanAktif = null;
let pointerAktif = null;
let status = null;
let sibuk = false;

// Kedua sumbu dibagi tinggi kanvas agar bentuk tidak melar pada kanvas yang tidak persegi.
function titikNormal(e) {
    return { x: e.offsetX / kanvas.clientHeight, y: e.offsetY / kanvas.clientHeight, t: e.timeStamp };
}

function keLayar(titik) {
    return [titik.x * kanvas.clientHeight, titik.y * kanvas.clientHeight];
}

function aturUkuran() {
    const rasio = window.devicePixelRatio || 1;
    kanvas.width = Math.round(kanvas.clientWidth * rasio);
    kanvas.height = Math.round(kanvas.clientHeight * rasio);
    ctx.setTransform(rasio, 0, 0, rasio, 0, 0);
    gambarUlang();
}

function gambarSegmen(a, b) {
    ctx.strokeStyle = gaya.getPropertyValue('--nk-indigo-tua');
    ctx.lineWidth = 14;
    ctx.lineCap = 'round';
    ctx.lineJoin = 'round';
    ctx.beginPath();
    ctx.moveTo(...keLayar(a));
    ctx.lineTo(...keLayar(b));
    ctx.stroke();
}

function gambarUlang() {
    ctx.clearRect(0, 0, kanvas.clientWidth, kanvas.clientHeight);
    if (sakelar.checked) {
        ctx.fillStyle = getComputedStyle(kanvas).getPropertyValue('--lt-outline');
        ctx.font = "230px 'Noto Sans Javanese'";
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        ctx.fillText(data.aksara, kanvas.clientWidth / 2, kanvas.clientHeight / 2);
    }
    for (const g of [...goresan, goresanAktif ?? []]) {
        for (let i = 1; i < g.length; i++) gambarSegmen(g[i - 1], g[i]);
    }
}

/** Algo-020. */
function rekamGoresan(titik) {
    goresanAktif.push(titik);
    const n = goresanAktif.length;
    if (n >= 2) {
        const [a, b] = [goresanAktif[n - 2], goresanAktif[n - 1]];
        requestAnimationFrame(() => gambarSegmen(a, b));
    }
}

function perbaruiTombol() {
    tombolPeriksa.disabled = sibuk || (status !== 'berhasil' && goresan.length === 0);
}

function aturBilah(keadaan, judul = '', pesan = '') {
    bilah.dataset.keadaan = keadaan;
    document.getElementById('hasil-judul').textContent = judul;
    document.getElementById('hasil-pesan').textContent = pesan;
    tombolPeriksa.textContent = keadaan === 'berhasil' ? 'Selesaikan' : 'Periksa';
}

/** Skenario Alternatif 3: goresan dihapus, soal dan outline tetap. */
function ulangiLatihan() {
    goresan.length = 0;
    goresanAktif = null;
    status = null;
    aturBilah('netral');
    gambarUlang();
    perbaruiTombol();
}

async function kirim(url, isi) {
    const respons = await fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': csrf },
        body: JSON.stringify(isi),
    });
    return { status: respons.status, isi: await respons.json().catch(() => ({})) };
}

async function kirimJawaban() {
    const r = await kirim(data.urlNilai, { idSoal: data.idSoal, goresan });
    if (r.status === 409) return aturBilah('galat', 'Soal tidak ditemukan', r.isi.message);
    if (r.status !== 200) return aturBilah('perlu_diulang', 'Belum terkirim', r.isi.message ?? 'Coba lagi.');
    status = r.isi.status;
    aturBilah(status, status === 'berhasil' ? 'Bagus!' : 'Belum tepat', r.isi.umpanBalik);
}

async function selesaikanLatihan() {
    const r = await kirim(data.urlSelesai, { idSoal: data.idSoal });
    if (r.status === 200 && r.isi.redirect) return window.location.assign(r.isi.redirect);
    aturBilah('perlu_diulang', 'Belum tersimpan', r.isi.message ?? 'Hasil belum tersimpan. Coba lagi.');
}

kanvas.addEventListener('pointerdown', (e) => {
    if (pointerAktif !== null || status === 'berhasil') return;
    pointerAktif = e.pointerId;
    kanvas.setPointerCapture(e.pointerId);
    goresanAktif = [];
    rekamGoresan(titikNormal(e));
});

kanvas.addEventListener('pointermove', (e) => {
    if (e.pointerId === pointerAktif) rekamGoresan(titikNormal(e));
});

function akhiriGoresan(e) {
    if (e.pointerId !== pointerAktif) return;
    pointerAktif = null;
    if (goresanAktif.length >= 2) goresan.push(goresanAktif);
    goresanAktif = null;
    perbaruiTombol();
}
kanvas.addEventListener('pointerup', akhiriGoresan);
kanvas.addEventListener('pointercancel', akhiriGoresan);

tombolPeriksa.addEventListener('click', async () => {
    sibuk = true;
    perbaruiTombol();
    try {
        await (status === 'berhasil' && data.urlSelesai ? selesaikanLatihan() : kirimJawaban());
    } finally {
        sibuk = false;
        perbaruiTombol();
    }
});

sakelar.addEventListener('change', gambarUlang);
document.getElementById('bersihkan').addEventListener('click', ulangiLatihan);
document.getElementById('ulangi').addEventListener('click', ulangiLatihan);
new ResizeObserver(aturUkuran).observe(kanvas);
document.fonts.load("230px 'Noto Sans Javanese'", data.aksara).then(gambarUlang);
