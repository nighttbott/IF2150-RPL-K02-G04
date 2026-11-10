#!/usr/bin/env python3
"""Skrip sekali jalan: ambil kontur aksara dari font Noto Sans lalu simpan sebagai
daftar titik JSON (template goresan untuk Algo-021 hitungAkurasiGoresan).

Pemakaian:
  pip install fonttools
  python buat_template_aksara.py FONT_JAWA.(ttf|woff) FONT_SUNDA.(ttf|woff) keluaran.json

Format keluaran (koordinat canvas: x ke kanan, y ke bawah, kotak 100 x 100,
rasio gambar dipertahankan dan dipusatkan):
{
  "versi": 2, "kotak": 100, "jarakTitik": 2.0,
  "aksara": [
    {"kode": "ha", "jenis": "JAWA", "kategori": "aksara", "karakter": "ꦲ", "unicode": "U+A9B2",
     "goresan": [ [ {"x": 12.3, "y": 40.1}, ... ], ... ]}   # satu goresan = satu kontur
  ]
}
"""
import json
import math
import sys

from fontTools.pens.basePen import BasePen
from fontTools.ttLib import TTFont

KOTAK = 100.0
JARAK = 2.0          # jarak antar titik hasil resampling (satuan kotak 100)
LANGKAH_KURVA = 24   # pecahan tiap kurva Bezier

# kode latin -> titik kode Unicode
JAWA = {"ha": 0xA9B2, "na": 0xA9A4, "ca": 0xA995, "ra": 0xA9AB, "ka": 0xA98F,
        "da": 0xA9A2, "ta": 0xA9A0, "sa": 0xA9B1, "wa": 0xA9AE, "la": 0xA9AD,
        "pa": 0xA9A5, "dha": 0xA99D, "ja": 0xA997, "ya": 0xA9AA, "nya": 0xA99A,
        "ma": 0xA9A9, "ga": 0xA992, "ba": 0xA9A7, "tha": 0xA99B, "nga": 0xA994}
SUNDA = {"ka": 0x1B8A, "ga": 0x1B8C, "nga": 0x1B8D, "ca": 0x1B8E, "ja": 0x1B8F,
         "nya": 0x1B91, "ta": 0x1B92, "da": 0x1B93, "na": 0x1B94, "pa": 0x1B95,
         "ba": 0x1B98, "ma": 0x1B99, "ya": 0x1B9A, "ra": 0x1B9B, "la": 0x1B9C,
         "wa": 0x1B9D, "sa": 0x1B9E, "ha": 0x1BA0}

# Sandhangan Jawa: kode -> (nama glyph di font, kode Unicode karakter). Glyph diambil langsung
# lewat nama (bukan cmap) agar tanda nirjarak dan bentuk pasangan bisa digambar sendirian.
# Nama glyph "la.pas" / "wa.pas" adalah bentuk pasangan hasil shaping, "taling" + "tarung" digabung.
SANDHANGAN_JAWA = {
    "wulu": (["i.ns"], "\uA9B6"), "suku": (["u.ns"], "\uA9B8"),
    "taling": (["taling"], "\uA9BA"), "taling_tarung": (["taling", "tarung"], "\uA9BA\uA9B4"),
    "pepet": (["ae.ns"], "\uA9BC"), "layar": (["layar.ns"], "\uA982"),
    "wignyan": (["wignyan"], "\uA983"), "cecak": (["cecak.ns"], "\uA981"),
    "pangkon": (["pangkon"], "\uA9C0"), "pada_lingsa": (["uniA9C8"], "\uA9C8"),
    "pada_lungsi": (["uniA9C9"], "\uA9C9"), "pada_pangkat": (["uniA9C7"], "\uA9C7"),
    "cakra": (["cakra"], "\uA9BF"), "cakra_keret": (["keret.ns"], "\uA9BD"),
    "pasangan_la": (["la.pas"], "\uA9C0\uA9AD"), "pasangan_wa": (["wa.pas"], "\uA9C0\uA9AE"),
    "pengkal": (["pengkal"], "\uA9BE"), "pa_cerek": (["pacerek"], "\uA989"),
    "nga_lelet": (["ngalelet"], "\uA98A"), "pada_adeg_adeg": (["uniA9CB"], "\uA9CB"),
}

SANDHANGAN_SUNDA = {
    "panyecek": (["uni1B80"], "\u1B80"), "panglayar": (["uni1B81"], "\u1B81"),
    "pangwisad": (["uni1B82"], "\u1B82"), "pamingkal": (["uni1BA1"], "\u1BA1"),
    "panyakra": (["uni1BA2"], "\u1BA2"), "panyiku": (["uni1BA3"], "\u1BA3"),
    "panghulu": (["uni1BA4"], "\u1BA4"), "panyuku": (["uni1BA5"], "\u1BA5"),
    "panaelaeng": (["uni1BA6"], "\u1BA6"), "panolong": (["uni1BA7"], "\u1BA7"),
    "pamepet": (["uni1BA8"], "\u1BA8"), "paneuleung": (["uni1BA9"], "\u1BA9"),
    "pamaaeh": (["uni1BAA"], "\u1BAA"),
}


class PenKontur(BasePen):
    """Mengubah outline glyph menjadi daftar kontur berisi titik (kurva diratakan)."""

    def __init__(self, glyph_set):
        super().__init__(glyph_set)
        self.kontur = []
        self._now = None

    def _moveTo(self, p):
        self._now = [p]

    def _lineTo(self, p):
        self._now.append(p)

    def _curveToOne(self, p1, p2, p3):
        p0 = self._now[-1]
        for i in range(1, LANGKAH_KURVA + 1):
            t = i / LANGKAH_KURVA
            u = 1 - t
            x = u**3 * p0[0] + 3 * u * u * t * p1[0] + 3 * u * t * t * p2[0] + t**3 * p3[0]
            y = u**3 * p0[1] + 3 * u * u * t * p1[1] + 3 * u * t * t * p2[1] + t**3 * p3[1]
            self._now.append((x, y))

    def _qCurveToOne(self, p1, p2):
        p0 = self._now[-1]
        for i in range(1, LANGKAH_KURVA + 1):
            t = i / LANGKAH_KURVA
            u = 1 - t
            x = u * u * p0[0] + 2 * u * t * p1[0] + t * t * p2[0]
            y = u * u * p0[1] + 2 * u * t * p1[1] + t * t * p2[1]
            self._now.append((x, y))

    def _closePath(self):
        if self._now and len(self._now) > 1:
            self._now.append(self._now[0])
            self.kontur.append(self._now)
        self._now = None

    def _endPath(self):
        if self._now and len(self._now) > 1:
            self.kontur.append(self._now)
        self._now = None


def resample(titik, jarak):
    """Ambil ulang titik sepanjang garis dengan jarak tetap."""
    panjang = [0.0]
    for a, b in zip(titik, titik[1:]):
        panjang.append(panjang[-1] + math.dist(a, b))
    total = panjang[-1]
    if total == 0:
        return [titik[0]]
    n = max(2, int(round(total / jarak)) + 1)
    hasil, j = [], 0
    for i in range(n):
        s = total * i / (n - 1)
        while j < len(panjang) - 2 and panjang[j + 1] < s:
            j += 1
        seg = panjang[j + 1] - panjang[j]
        f = 0 if seg == 0 else (s - panjang[j]) / seg
        a, b = titik[j], titik[j + 1]
        hasil.append((a[0] + (b[0] - a[0]) * f, a[1] + (b[1] - a[1]) * f))
    return hasil


def ambil_aksara(font, kode_unicode):
    cmap = font.getBestCmap()
    nama = cmap[kode_unicode]
    gs = font.getGlyphSet()
    pen = PenKontur(gs)
    gs[nama].draw(pen)
    return pen.kontur


def normalisasi(kontur):
    """Skala ke kotak 100x100 (rasio tetap), pusatkan, balik sumbu y untuk canvas."""
    semua = [p for k in kontur for p in k]
    x0, x1 = min(p[0] for p in semua), max(p[0] for p in semua)
    y0, y1 = min(p[1] for p in semua), max(p[1] for p in semua)
    skala = KOTAK / max(x1 - x0, y1 - y0)
    ox = (KOTAK - (x1 - x0) * skala) / 2
    oy = (KOTAK - (y1 - y0) * skala) / 2
    hasil = []
    for k in kontur:
        hasil.append([((p[0] - x0) * skala + ox, (y1 - p[1]) * skala + oy) for p in k])
    return hasil


def bangun(font, peta, jenis):
    daftar = []
    for kode, cp in peta.items():
        kontur = normalisasi(ambil_aksara(font, cp))
        goresan = []
        for k in kontur:
            r = resample(k, JARAK)
            goresan.append([{"x": round(x, 2), "y": round(y, 2)} for x, y in r])
        daftar.append({"kode": kode, "jenis": jenis, "kategori": "aksara", "karakter": chr(cp),
                       "unicode": "U+%04X" % cp, "goresan": goresan})
    return daftar


def ambil_glyph(font, daftar_nama):
    """Gambar satu atau beberapa glyph berurutan (jarak antar glyph = lebar maju glyph sebelumnya)."""
    from fontTools.pens.transformPen import TransformPen
    gs = font.getGlyphSet()
    pen = PenKontur(gs)
    x = 0
    for nama in daftar_nama:
        gs[nama].draw(TransformPen(pen, (1, 0, 0, 1, x, 0)))
        x += gs[nama].width
    return pen.kontur


def bangun_sandhangan(font, peta, jenis):
    daftar = []
    for kode, (glyph, teks) in peta.items():
        kontur = normalisasi(ambil_glyph(font, glyph))
        goresan = []
        for k in kontur:
            r = resample(k, JARAK)
            goresan.append([{"x": round(x, 2), "y": round(y, 2)} for x, y in r])
        daftar.append({"kode": kode, "jenis": jenis, "kategori": "sandhangan", "karakter": teks,
                       "unicode": " ".join("U+%04X" % ord(c) for c in teks), "goresan": goresan})
    return daftar


def main():
    if len(sys.argv) != 4:
        sys.exit(__doc__)
    jawa, sunda, keluar = sys.argv[1:4]
    font_jawa, font_sunda = TTFont(jawa), TTFont(sunda)
    data = {"versi": 2, "kotak": 100, "jarakTitik": JARAK, "aksara":
            bangun(font_jawa, JAWA, "JAWA") + bangun_sandhangan(font_jawa, SANDHANGAN_JAWA, "JAWA")
            + bangun(font_sunda, SUNDA, "SUNDA") + bangun_sandhangan(font_sunda, SANDHANGAN_SUNDA, "SUNDA")}
    with open(keluar, "w", encoding="utf-8") as f:
        json.dump(data, f, ensure_ascii=False, separators=(",", ":"))
    print("selesai:", len(data["aksara"]), "aksara ->", keluar)


if __name__ == "__main__":
    main()
