#!/usr/bin/env python3
"""Skrip sekali jalan: ambil kontur aksara dari font Noto Sans lalu simpan sebagai
daftar titik JSON (template goresan untuk Algo-021 hitungAkurasiGoresan).

Pemakaian:
  pip install fonttools
  python buat_template_aksara.py FONT_JAWA.(ttf|woff) FONT_SUNDA.(ttf|woff) keluaran.json

Format keluaran (koordinat canvas: x ke kanan, y ke bawah, kotak 100 x 100,
rasio gambar dipertahankan dan dipusatkan):
{
  "versi": 1, "kotak": 100, "jarakTitik": 2.0,
  "aksara": [
    {"kode": "ha", "jenis": "JAWA", "karakter": "ꦲ", "unicode": "U+A9B2",
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
        daftar.append({"kode": kode, "jenis": jenis, "karakter": chr(cp),
                       "unicode": "U+%04X" % cp, "goresan": goresan})
    return daftar


def main():
    if len(sys.argv) != 4:
        sys.exit(__doc__)
    jawa, sunda, keluar = sys.argv[1:4]
    data = {"versi": 1, "kotak": 100, "jarakTitik": JARAK, "aksara":
            bangun(TTFont(jawa), JAWA, "JAWA") + bangun(TTFont(sunda), SUNDA, "SUNDA")}
    with open(keluar, "w", encoding="utf-8") as f:
        json.dump(data, f, ensure_ascii=False, separators=(",", ":"))
    print("selesai:", len(data["aksara"]), "aksara ->", keluar)


if __name__ == "__main__":
    main()
