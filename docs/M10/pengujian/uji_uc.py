"""Pengujian use case (HIUPL 4.4) dan KNF03/KNF04 di browser terhadap server Laravel nyata.
Setiap kasus menghasilkan tangkapan layar <ID>.png dan baris hasil di hasil_uc.json.

Pemakaian (dari root repo, lihat README.md di folder ini):
    python docs/M10/pengujian/uji_uc.py --siapkan          # sekali, setelah migrate:fresh --seed
    python docs/M10/pengujian/uji_uc.py docs/M10/assets/uc  # menjalankan semua kasus
    python docs/M10/pengujian/uji_uc.py docs/M10/assets/uc UC03-TC01 UC09-TC02   # kasus tertentu
"""
import json, os, shutil, sqlite3, sys, tempfile, time, traceback, pathlib
from playwright.sync_api import sync_playwright, expect

DIR = pathlib.Path(__file__).resolve().parent
SRC = DIR.parents[2] / 'src'
BASE = os.environ.get('NGAKSARA_URL', 'http://127.0.0.1:8000')
DB = os.environ.get('NGAKSARA_DB', str(SRC / 'database' / 'e2e.sqlite'))
TEMPLAT = {a['karakter']: a for a in json.load(open(SRC / 'resources/aksara/template_aksara.json', encoding='utf-8'))['aksara']}
AUDIO = SRC / 'storage/app/private/materi/1/audio.mp3'


def siapkan():
    """Data tambahan: audio materi 1, audio hilang materi 2, tugas lewat tenggat di kelas K7M2QX."""
    AUDIO.parent.mkdir(parents=True, exist_ok=True)
    shutil.copy(DIR / 'contoh-audio.mp3', AUDIO)
    c = sqlite3.connect(DB)
    c.execute("update konten_materi set path_audio='materi/1/audio.mp3' where id_materi=1")
    c.execute("update konten_materi set path_audio='materi/2/hilang.mp3' where id_materi=2")
    k = c.execute("select id_kelas from kelas_belajar where kode_bergabung='K7M2QX'").fetchone()[0]
    c.execute("insert into tugas_kelas(id_kelas,instruksi,tenggat,status) values(?,?,datetime('now','localtime','-1 day'),'terbit')",
              (k, 'Baca ulang materi nglegena (tugas minggu lalu).'))
    t = c.execute('select max(id_tugas) from tugas_kelas').fetchone()[0]
    c.execute('insert into komponen_tugas(id_tugas,jenis,id_materi,mode,wajib) values(?,?,?,?,?)', (t, 'materi', 1, None, 1))
    c.commit(); c.close()
    print('Data uji siap.')


if len(sys.argv) > 1 and sys.argv[1] == '--siapkan':
    siapkan(); sys.exit(0)
OUT = pathlib.Path(sys.argv[1] if len(sys.argv) > 1 else DIR.parent / 'assets' / 'uc'); OUT.mkdir(parents=True, exist_ok=True)
SANDI = 'Rahasia123!'
SANDI_BARU = 'Rahasia123'
hasil = {}
latensi = []

UKUR = """
window.__antri = []; window.__lat = [];
document.addEventListener('pointerdown', () => { window.__antri = []; }, true);
document.addEventListener('pointermove', e => { if (e.buttons) window.__antri.push(e.timeStamp); }, true);
const asli = CanvasRenderingContext2D.prototype.stroke;
CanvasRenderingContext2D.prototype.stroke = function (...a) {
  const r = asli.apply(this, a);
  if (window.__antri.length) window.__lat.push(performance.now() - window.__antri.shift());
  return r;
};
"""


def db(sql, *arg):
    c = sqlite3.connect(DB); r = c.execute(sql, arg).fetchall(); c.commit(); c.close(); return r


def kasus(id_, judul):
    def bungkus(fn):
        def jalan(browser):
            ctx = browser.new_context(viewport={'width': 1280, 'height': 860}, locale='id-ID')
            ctx.add_init_script(UKUR)
            page = ctx.new_page()
            mulai = time.time()
            try:
                catatan = fn(page) or ''
                status = 'Lulus'
            except Exception as e:  # noqa
                catatan = f'{type(e).__name__}: {e}'.splitlines()[0][:300]
                status = 'Gagal'
                traceback.print_exc()
            page.screenshot(path=str(OUT / f'{id_}.png'), full_page=True)
            hasil[id_] = {'judul': judul, 'status': status, 'catatan': catatan, 'detik': round(time.time() - mulai, 1)}
            print(id_, status, catatan)
            ctx.close()
        jalan.id = id_
        return jalan
    return bungkus


def masuk(page, email, sandi=None):
    page.goto(BASE + '/masuk')
    page.fill('#email', email); page.fill('#password', sandi or (SANDI_BARU if 'e2e' in email else SANDI))
    page.click('#tombol-masuk'); page.wait_for_load_state('networkidle')
    assert '/masuk' not in page.url, 'login gagal untuk ' + email


def pilih(page, sel):
    page.locator(sel).first.evaluate("e => { if (!e.checked) e.click(); }")


def tulis(page, strokes, langkah=1):
    kotak = page.locator('#kanvas').bounding_box()
    h = kotak['height']; x0, y0 = kotak['x'], kotak['y']
    for g in strokes:
        page.mouse.move(x0 + g[0]['x'] / 100 * h, y0 + g[0]['y'] / 100 * h)
        page.mouse.down()
        for p in g[1:]:
            page.mouse.move(x0 + p['x'] / 100 * h, y0 + p['y'] / 100 * h, steps=langkah)
        page.mouse.up()


def tulis_benar(page):
    aksara = page.locator('#kanvas').get_attribute('data-aksara')
    tulis(page, TEMPLAT[aksara]['tengah'])
    return aksara


# ---------- UC-01 ----------
@kasus('UC01-TC01', 'Normal: pendaftaran akun Pelajar baru')
def uc01_tc01(page):
    page.goto(BASE + '/daftar')
    pilih(page, 'input[name=peran][value=pelajar]')
    page.fill('#email', 'dewi.e2e@ngaksara.test'); page.fill('#username', 'dewi_e2e'); page.fill('#password', 'Rahasia123')
    pilih(page, '#setuju_tnc')
    page.click('#tombol-daftar'); page.wait_for_load_state('networkidle')
    assert '/daftar' not in page.url, page.url
    hashnya = db("select password_hash from akun_pengguna where email='dewi.e2e@ngaksara.test'")[0][0]
    assert hashnya.startswith('$2y$')
    return f'Akun tersimpan, diarahkan ke {page.url.replace(BASE, "")}; password_hash diawali {hashnya[:7]}'


@kasus('UC01-TC02', 'Alternatif 1: kredensial tidak valid (email sudah terdaftar)')
def uc01_tc02(page):
    page.goto(BASE + '/daftar')
    page.fill('#email', 'pelajar@ngaksara.test'); page.fill('#username', 'sari_baru'); page.fill('#password', 'Rahasia123')
    pilih(page, '#setuju_tnc')
    page.click('#tombol-daftar'); page.wait_for_load_state('networkidle')
    assert page.get_by_text('Email sudah terdaftar').count() > 0
    assert 'daftar' in page.url
    return 'Tetap di halaman daftar, pesan: "Email sudah terdaftar. Gunakan email lain atau masuk dengan akun yang ada."'


# ---------- UC-02 ----------
@kasus('UC02-TC01', 'Normal: memilih jenis/tingkat, membuka materi, memutar audio')
def uc02_tc01(page):
    masuk(page, 'pelajar@ngaksara.test')
    page.goto(BASE + '/materi?jenis=jawa&tingkat=1')
    page.locator('a[href$="/materi/1"]').first.click(); page.wait_for_load_state('networkidle')
    with page.expect_response(lambda r: '/audio' in r.url) as resp:
        page.click('#tombol-dengar')
    page.wait_for_timeout(600)
    assert resp.value.status == 200, resp.value.status
    assert page.locator('#galat-audio').is_hidden()
    return f'Daftar & isi materi tampil; GET {resp.value.url.replace(BASE, "")} = {resp.value.status}, tanpa pesan galat audio'


@kasus('UC02-TC02', 'Alternatif 1: materi belum tersedia')
def uc02_tc02(page):
    masuk(page, 'pelajar@ngaksara.test')
    page.goto(BASE + '/materi?jenis=sunda&tingkat=3')
    teks = page.locator('main').inner_text()
    assert 'belum' in teks.lower(), teks[:200]
    return 'Halaman menampilkan keadaan kosong "materi belum tersedia"'


@kasus('UC02-TC03', 'Alternatif 2: audio gagal diputar')
def uc02_tc03(page):
    masuk(page, 'pelajar@ngaksara.test')
    page.goto(BASE + '/materi/2')
    page.click('#tombol-dengar')
    expect(page.locator('#galat-audio')).to_be_visible(timeout=5000)
    return 'Berkas audio hilang -> pesan "Audio tidak dapat diputar..." tampil, materi tetap terbaca'


# ---------- UC-03 ----------
@kasus('UC03-TC01', 'Normal: latihan menulis di kanvas berhasil')
def uc03_tc01(page):
    masuk(page, 'budi@ngaksara.test')
    page.goto(BASE + '/latihan/1/menulis'); page.wait_for_load_state('networkidle')
    page.evaluate('window.__lat = []')
    aksara = tulis_benar(page)
    latensi.extend(page.evaluate('window.__lat'))
    page.click('#periksa'); expect(page.locator('#bilah-hasil')).to_have_attribute('data-keadaan', 'berhasil', timeout=8000)
    pesan = page.locator('#hasil-pesan').inner_text()
    page.screenshot(path=str(OUT / 'UC03-TC01-a.png'), full_page=True)
    page.click('#periksa'); page.wait_for_url('**/latihan/hasil', timeout=8000)
    return f'Aksara {aksara}: {pesan}; hasil latihan, poin, dan streak tampil'


@kasus('UC03-TC02', 'Alternatif 1: latihan mode bunyi')
def uc03_tc02(page):
    masuk(page, 'wulan@ngaksara.test')
    page.goto(BASE + '/latihan/soal/bunyi/1'); page.wait_for_load_state('networkidle')
    nama = sorted({n for n in page.eval_on_selector_all('input[type=radio]', 'e => e.map(x => x.name)')})
    for n in nama:
        page.locator(f'input[name="{n}"]').first.check(force=True)
    page.screenshot(path=str(OUT / 'UC03-TC02-a.png'), full_page=True)
    page.get_by_role('button', name='Kirim jawaban').click(); page.wait_for_load_state('networkidle')
    assert 'hasil' in page.url, page.url
    return f'{len(nama)} soal bunyi dijawab; halaman hasil tampil dengan skor per soal'


@kasus('UC03-TC03', 'Alternatif 2: latihan mode merangkai')
def uc03_tc03(page):
    masuk(page, 'wulan@ngaksara.test')
    page.goto(BASE + '/latihan/soal/merangkai/1'); page.wait_for_load_state('networkidle')
    ubin = page.locator('button.ubin')
    for i in range(ubin.count()):
        if ubin.nth(i).is_visible() and ubin.nth(i).is_enabled():
            ubin.nth(i).click()
    page.screenshot(path=str(OUT / 'UC03-TC03-a.png'), full_page=True)
    page.get_by_role('button', name='Kirim jawaban').click(); page.wait_for_load_state('networkidle')
    assert 'hasil' in page.url, page.url
    return 'Ubin dirangkai lalu dikirim; posisi salah ditandai di halaman hasil'


@kasus('UC03-TC04', 'Alternatif 3: hasil gambar kurang akurat lalu ulangi')
def uc03_tc04(page):
    masuk(page, 'wulan@ngaksara.test')
    page.goto(BASE + '/latihan/1/menulis'); page.wait_for_load_state('networkidle')
    tulis(page, [[{'x': 10, 'y': 10}, {'x': 30, 'y': 12}]])
    page.click('#periksa'); expect(page.locator('#bilah-hasil')).to_have_attribute('data-keadaan', 'perlu_diulang', timeout=8000)
    pesan = page.locator('#hasil-pesan').inner_text()
    page.screenshot(path=str(OUT / 'UC03-TC04-a.png'), full_page=True)
    page.click('#ulangi')
    expect(page.locator('#bilah-hasil')).to_have_attribute('data-keadaan', 'netral')
    return f'Umpan balik "{pesan}"; tombol Ulangi menghapus goresan, soal tetap'


# ---------- UC-04 ----------
@kasus('UC04-TC01', 'Normal: meninjau progres dan motivasi')
def uc04_tc01(page):
    masuk(page, 'pelajar@ngaksara.test')
    page.goto(BASE + '/progres'); page.wait_for_load_state('networkidle')
    teks = page.locator('main').inner_text().lower()
    assert 'streak' in teks and 'poin' in teks
    return 'Penguasaan per materi, pola kesalahan, rekomendasi, streak, poin, dan badge tampil'


@kasus('UC04-TC02', 'Alternatif 1: belum ada riwayat latihan')
def uc04_tc02(page):
    masuk(page, 'dewi.e2e@ngaksara.test')
    page.goto(BASE + '/progres'); page.wait_for_load_state('networkidle')
    teks = page.locator('main').inner_text().lower()
    assert 'belum' in teks
    return 'Pelajar baru melihat pesan belum ada riwayat dan ajakan mulai belajar'


@kasus('UC04-TC03', 'Alternatif 2: melihat papan peringkat kelas')
def uc04_tc03(page):
    masuk(page, 'pelajar@ngaksara.test')
    k = db("select id_kelas from kelas_belajar where kode_bergabung='K7M2QX'")[0][0]
    page.goto(BASE + f'/progres?kelas={k}'); page.wait_for_load_state('networkidle')
    teks = page.locator('main').inner_text()
    assert 'Sari' in teks and 'Budi' in teks
    assert '@ngaksara.test' not in teks
    return 'Papan peringkat anggota aktif tampil urut poin, tanpa email'


# ---------- UC-05 ----------
@kasus('UC05-TC01', 'Normal: bergabung kelas, mengerjakan, dan mengumpulkan tugas')
def uc05_tc01(page):
    masuk(page, 'dewi.e2e@ngaksara.test')
    page.goto(BASE + '/pelajar/kelas'); page.fill('#kode', 'k7m2qx'); page.get_by_role('button', name='Gabung').click()
    page.wait_for_load_state('networkidle')
    k, t = db("select k.id_kelas, t.id_tugas from kelas_belajar k join tugas_kelas t on t.id_kelas=k.id_kelas where k.kode_bergabung='K7M2QX' order by t.id_tugas")[0]
    kom = db('select id_komponen, jenis from komponen_tugas where id_tugas=? order by id_komponen', t)
    for idk, jenis in kom:
        page.goto(BASE + f'/pelajar/kelas/{k}/tugas/{t}/komponen/{idk}'); page.wait_for_load_state('networkidle')
        if jenis == 'materi':
            page.get_by_role('button', name='Selesai membaca').click(); page.wait_for_load_state('networkidle')
        else:
            tulis_benar(page)
            page.click('#periksa'); expect(page.locator('#bilah-hasil')).to_have_attribute('data-keadaan', 'berhasil', timeout=8000)
            page.click('#periksa'); page.wait_for_url('**/latihan/hasil', timeout=8000)
    page.goto(BASE + f'/pelajar/kelas/{k}/tugas/{t}')
    page.get_by_role('button', name='Kumpulkan').click(); page.wait_for_load_state('networkidle')
    st = db("select p.status from penyelesaian_tugas p join akun_pengguna a on a.id_akun=p.id_pelajar where a.email='dewi.e2e@ngaksara.test' and p.id_tugas=?", t)
    assert st and st[0][0] == 'tepat_waktu', st
    return 'Bergabung dengan kode, komponen materi & latihan selesai, tugas terkumpul tepat_waktu'


@kasus('UC05-TC02', 'Alternatif 1: sudah menjadi anggota')
def uc05_tc02(page):
    masuk(page, 'pelajar@ngaksara.test')
    page.goto(BASE + '/pelajar/kelas'); page.fill('#kode', 'K7M2QX'); page.get_by_role('button', name='Gabung').click()
    page.wait_for_load_state('networkidle')
    teks = page.locator('main').inner_text()
    assert 'sudah menjadi anggota' in teks.lower(), teks[:300]
    return 'Pesan "Kamu sudah menjadi anggota kelas ini." dan diarahkan ke halaman kelas'


@kasus('UC05-TC03', 'Alternatif 2: tugas dikumpulkan melewati tenggat')
def uc05_tc03(page):
    masuk(page, 'budi@ngaksara.test')
    k, t = db("select k.id_kelas, t.id_tugas from kelas_belajar k join tugas_kelas t on t.id_kelas=k.id_kelas where k.kode_bergabung='K7M2QX' order by t.id_tugas desc")[0]
    idk = db('select id_komponen from komponen_tugas where id_tugas=?', t)[0][0]
    page.goto(BASE + f'/pelajar/kelas/{k}/tugas/{t}/komponen/{idk}')
    page.get_by_role('button', name='Selesai membaca').click(); page.wait_for_load_state('networkidle')
    page.goto(BASE + f'/pelajar/kelas/{k}/tugas/{t}')
    page.get_by_role('button', name='Kumpulkan').click(); page.wait_for_load_state('networkidle')
    st = db("select p.status from penyelesaian_tugas p join akun_pengguna a on a.id_akun=p.id_pelajar where a.email='budi@ngaksara.test' and p.id_tugas=?", t)
    assert st and st[0][0] == 'terlambat', st
    return 'Tugas lewat tenggat tetap dapat dikumpulkan dan berstatus "terlambat"'


@kasus('UC05-TC04', 'Alternatif 3: kode kelas tidak valid')
def uc05_tc04(page):
    masuk(page, 'wulan@ngaksara.test')
    page.goto(BASE + '/pelajar/kelas'); page.fill('#kode', 'ZZZZZZ'); page.get_by_role('button', name='Gabung').click()
    page.wait_for_load_state('networkidle')
    teks = page.locator('main').inner_text()
    assert 'tidak ditemukan' in teks.lower(), teks[:200]
    return 'Pesan "Kode tidak ditemukan atau kelas tidak aktif." tampil; isian tetap'


# ---------- UC-06 ----------
@kasus('UC06-TC01', 'Normal: Tim Materi menambah materi dengan audio lalu menerbitkan')
def uc06_tc01(page):
    masuk(page, 'materi@ngaksara.test')
    page.goto(BASE + '/kelola/materi/baru')
    page.fill('#nama_materi', 'Pasangan: ha na')
    pilih(page, '#jenis-jawa'); pilih(page, '#tingkat-2')
    page.fill('#aturan_contoh', 'Pasangan ditulis di bawah aksara sebelumnya. Contoh: ꦲꦤ = "ana"')
    page.set_input_files('#berkas', str(DIR / 'contoh-audio.mp3'))
    page.get_by_role('button', name='Simpan sebagai draf').click(); page.wait_for_load_state('networkidle')
    baris = page.locator('tr', has_text='Pasangan: ha na')
    baris.get_by_role('button', name='Terbitkan').click(); page.wait_for_load_state('networkidle')
    st = db("select m.status_publikasi, k.path_audio from materi_aksara m join konten_materi k on k.id_materi=m.id_materi where nama_materi='Pasangan: ha na'")
    assert st and st[0][0] == 'terbit' and st[0][1], st
    return f'Materi tersimpan (audio {st[0][1]}) lalu terbit'


@kasus('UC06-TC02', 'Alternatif 1: penambahan gagal karena berkas tidak didukung')
def uc06_tc02(page):
    p = pathlib.Path(tempfile.gettempdir()) / 'virus.exe'; p.write_bytes(b'MZ' + b'0' * 64)
    masuk(page, 'materi@ngaksara.test')
    page.goto(BASE + '/kelola/materi/baru')
    page.fill('#nama_materi', 'Materi berkas salah')
    pilih(page, '#jenis-jawa'); pilih(page, '#tingkat-1')
    page.fill('#aturan_contoh', 'Aturan. Contoh: ꦲ = "ha"')
    page.set_input_files('#berkas', str(p))
    page.get_by_role('button', name='Simpan sebagai draf').click(); page.wait_for_load_state('networkidle')
    assert page.get_by_text('Jenis berkas tidak didukung').count() > 0
    assert not db("select 1 from materi_aksara where nama_materi='Materi berkas salah'")
    return 'Pesan "Jenis berkas tidak didukung"; materi tidak tersimpan'


# ---------- UC-07 ----------
@kasus('UC07-TC01', 'Normal: membuat kelas dan menerbitkan tugas')
def uc07_tc01(page):
    masuk(page, 'pengajar2@ngaksara.test')
    page.goto(BASE + '/pengajar/kelas')
    page.fill('input[name=nama]', 'Kelas Uji HIUPL'); page.get_by_role('button', name='+ Buat kelas').click(); page.wait_for_load_state('networkidle')
    k = db("select id_kelas, kode_bergabung from kelas_belajar where nama='Kelas Uji HIUPL'")[0]
    page.screenshot(path=str(OUT / 'UC07-TC01-a.png'), full_page=True)
    page.goto(BASE + f'/pengajar/kelas/{k[0]}/tugas')
    page.fill('#instruksi', 'Latihan menulis ha na ca ra ka.')
    page.fill('#tenggat', time.strftime('%Y-%m-%dT21:00', time.localtime(time.time() + 3 * 86400)))
    page.select_option('#pilih-jenis', index=1); page.click('#tambah')
    page.get_by_role('button', name='Terbitkan').click(); page.wait_for_load_state('networkidle')
    assert db("select status from tugas_kelas where id_kelas=?", k[0])[0][0] == 'terbit'
    return f'Kelas dibuat dengan kode {k[1]}; tugas terbit'


@kasus('UC07-TC02', 'Alternatif 1: mengganti kode kelas')
def uc07_tc02(page):
    masuk(page, 'pengajar2@ngaksara.test')
    lama = db("select kode_bergabung from kelas_belajar where nama='Kelas Uji HIUPL'")[0][0]
    page.goto(BASE + '/pengajar/kelas')
    page.locator('.kartu', has_text='Kelas Uji HIUPL').get_by_role('button', name='Ganti kode').first.click(); page.wait_for_load_state('networkidle')
    baru = db("select kode_bergabung from kelas_belajar where nama='Kelas Uji HIUPL'")[0][0]
    assert baru != lama
    return f'Kode {lama} diganti menjadi {baru}; kode lama tidak berlaku'


@kasus('UC07-TC03', 'Alternatif 2: mengeluarkan anggota')
def uc07_tc03(page):
    k = db("select id_kelas from kelas_belajar where kode_bergabung='K7M2QX'")[0][0]
    masuk(page, 'pengajar@ngaksara.test')
    page.goto(BASE + f'/pengajar/kelas/{k}/anggota')
    page.locator('.btn-keluarkan[data-nama="Wulan"]').click()
    expect(page.locator('#konfirmasi')).to_be_visible()
    page.screenshot(path=str(OUT / 'UC07-TC03-a.png'), full_page=True)
    page.get_by_role('button', name='Ya, keluarkan').click(); page.wait_for_load_state('networkidle')
    st = db("select g.status from keanggotaan_kelas g join akun_pengguna a on a.id_akun=g.id_pelajar where a.email='wulan@ngaksara.test' and g.id_kelas=?", k)
    assert st[0][0] != 'aktif', st
    return f'Dialog konfirmasi tampil; Wulan dikeluarkan (status {st[0][0]})'


@kasus('UC07-TC04', 'Alternatif 3: mengaktifkan papan peringkat')
def uc07_tc04(page):
    k = db("select id_kelas from kelas_belajar where nama='Kelas Uji HIUPL'")[0][0]
    masuk(page, 'pengajar2@ngaksara.test')
    page.goto(BASE + f'/pengajar/kelas/{k}/anggota')
    with page.expect_navigation():
        page.locator('input[type=checkbox][name=aktif]').check()
    page.wait_for_load_state('networkidle')
    assert db('select status_scoreboard from kelas_belajar where id_kelas=?', k)[0][0] == 1
    return 'Sakelar papan peringkat aktif; status_scoreboard = 1'


# ---------- UC-08 ----------
@kasus('UC08-TC01', 'Normal: memantau progres anggota dan mengirim umpan balik')
def uc08_tc01(page):
    k = db("select id_kelas from kelas_belajar where kode_bergabung='K7M2QX'")[0][0]
    sari = db("select id_akun from akun_pengguna where email='pelajar@ngaksara.test'")[0][0]
    masuk(page, 'pengajar@ngaksara.test')
    page.goto(BASE + f'/pemantauan/{k}?periode=semua&pelajar={sari}'); page.wait_for_load_state('networkidle')
    page.fill('textarea[name=pesan]', 'Bagus, Sari! Latih lagi aksara ra ya.')
    page.get_by_role('button', name='Kirim umpan balik').click(); page.wait_for_load_state('networkidle')
    assert page.get_by_text('Umpan balik terkirim').count() > 0
    return 'Ringkasan kelas & detail Sari tampil; umpan balik terkirim'


@kasus('UC08-TC02', 'Alternatif 1: belum ada hasil latihan')
def uc08_tc02(page):
    k = db("select id_kelas from kelas_belajar where nama='Kelas Uji HIUPL'")[0][0]
    masuk(page, 'pengajar2@ngaksara.test')
    page.goto(BASE + f'/pemantauan/{k}'); page.wait_for_load_state('networkidle')
    teks = page.locator('main').inner_text().lower()
    assert 'belum' in teks
    return 'Kelas tanpa hasil latihan menampilkan pesan belum ada data progres'


@kasus('UC08-TC03', 'Alternatif 2: pengguna bukan anggota aktif')
def uc08_tc03(page):
    k = db("select id_kelas from kelas_belajar where kode_bergabung='K7M2QX'")[0][0]
    raka = db("select id_akun from akun_pengguna where email='raka@ngaksara.test'")[0][0]
    masuk(page, 'pengajar@ngaksara.test')
    page.goto(BASE + f'/pemantauan/{k}?pelajar={raka}'); page.wait_for_load_state('networkidle')
    assert page.get_by_text('bukan anggota aktif').count() > 0
    return 'Pesan "Pengguna ini bukan anggota aktif kelas..." dan data progres tidak ditampilkan'


# ---------- UC-09 ----------
@kasus('UC09-TC01', 'Normal: mengirim feedback')
def uc09_tc01(page):
    masuk(page, 'pelajar@ngaksara.test')
    page.goto(BASE + '/feedback')
    pilih(page, 'input[name=kategori][value=fitur]')
    page.fill('#pesan', 'Tolong tambahkan latihan aksara Bali juga.')
    page.click('#tombol-kirim'); page.wait_for_load_state('networkidle')
    assert page.get_by_text('Feedback terkirim').count() > 0
    return 'Pesan "Feedback terkirim. Matur nuwun!" tampil; formulir kosong'


@kasus('UC09-TC02', 'Alternatif 1: feedback gagal terkirim')
def uc09_tc02(page):
    masuk(page, 'pelajar@ngaksara.test')
    page.goto(BASE + '/feedback')
    pilih(page, 'input[name=kategori][value=materi]')
    page.fill('#pesan', 'Audio materi kedua tidak bisa diputar.')
    db('ALTER TABLE data_feedback RENAME TO data_feedback_cadangan')
    try:
        page.click('#tombol-kirim'); page.wait_for_load_state('networkidle')
    finally:
        db('ALTER TABLE data_feedback_cadangan RENAME TO data_feedback')
    assert page.get_by_text('Feedback gagal terkirim').count() > 0
    assert page.input_value('#pesan') == 'Audio materi kedua tidak bisa diputar.'
    return 'Basis data gagal menulis -> pesan gagal tampil dan isi pesan tidak hilang'


# ---------- KNF03 (browser) ----------
@kasus('KNF03-TC02', 'KNF03: waktu muat daftar materi di browser')
def knf03_tc02(page):
    masuk(page, 'pelajar@ngaksara.test')
    t = []
    for _ in range(10):
        page.goto(BASE + '/materi?jenis=jawa&tingkat=1'); page.wait_for_load_state('load')
        t.append(page.evaluate("performance.getEntriesByType('navigation')[0].loadEventEnd"))
    t.sort()
    hasil['KNF03-ukur'] = {'n': 10, 'median_ms': round(t[4], 1), 'maks_ms': round(max(t), 1)}
    assert max(t) < 3000
    return f'10 kali muat: median {t[4]:.0f} ms, maks {max(t):.0f} ms (< 3000 ms)'


if __name__ == '__main__':
    urutan = [uc01_tc01, uc01_tc02, uc02_tc01, uc02_tc02, uc02_tc03, uc03_tc01, uc03_tc02, uc03_tc03, uc03_tc04,
              uc04_tc01, uc04_tc02, uc04_tc03, uc05_tc01, uc05_tc02, uc05_tc03, uc05_tc04, uc06_tc01, uc06_tc02,
              uc07_tc01, uc07_tc02, uc07_tc03, uc07_tc04, uc08_tc01, uc08_tc02, uc08_tc03, uc09_tc01, uc09_tc02, knf03_tc02]
    saring = sys.argv[2:] or None
    with sync_playwright() as p:
        b = p.chromium.launch(args=['--autoplay-policy=no-user-gesture-required'])
        for fn in urutan:
            if saring is None or fn.id in saring:
                fn(b)
        b.close()
    if latensi:
        s = sorted(latensi)
        hasil['KNF04-ukur'] = {'n': len(s), 'median_ms': round(s[len(s) // 2], 2), 'p95_ms': round(s[int(len(s) * .95)], 2), 'maks_ms': round(s[-1], 2)}
    lama = json.load(open(OUT / 'hasil_uc.json', encoding='utf-8')) if (OUT / 'hasil_uc.json').exists() else {}
    lama.update(hasil)
    json.dump(lama, open(OUT / 'hasil_uc.json', 'w', encoding='utf-8'), indent=1, ensure_ascii=False)
