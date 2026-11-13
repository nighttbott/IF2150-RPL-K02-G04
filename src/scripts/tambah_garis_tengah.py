#!/usr/bin/env python3
# Skrip sekali jalan: menambah field "tengah" (garis tengah huruf) ke template_aksara.json; pemakaian: pip install numpy, lalu python tambah_garis_tengah.py resources/aksara/template_aksara.json
import json
import math
import sys

import numpy as np

N = 200          # resolusi raster kotak 100 x 100
JARAK = 1.5      # jarak antartitik hasil (satuan kotak 100)
MIN_PIKSEL = 4   # cabang skeleton yang lebih pendek diabaikan


def raster(kontur):
    # isi huruf dengan aturan even-odd dari seluruh kontur (lubang ikut terhitung)
    yy, xx = np.mgrid[0:N, 0:N]
    px = (xx + 0.5) / N * 100
    py = (yy + 0.5) / N * 100
    dalam = np.zeros((N, N), bool)
    for g in kontur:
        pts = [(p['x'], p['y']) for p in g]
        for (x1, y1), (x2, y2) in zip(pts, pts[1:] + pts[:1]):
            if y1 != y2:
                dalam ^= ((y1 > py) != (y2 > py)) & (px < (x2 - x1) * (py - y1) / (y2 - y1) + x1)
    return dalam


def zhang_suen(img):
    # penipisan Zhang-Suen sampai tersisa garis selebar satu piksel
    img = img.astype(np.uint8)
    while True:
        ubah = False
        for langkah in (0, 1):
            p = np.pad(img, 1)
            nb = [p[:-2, 1:-1], p[:-2, 2:], p[1:-1, 2:], p[2:, 2:], p[2:, 1:-1], p[2:, :-2], p[1:-1, :-2], p[:-2, :-2]]
            P2, _, P4, _, P6, _, P8, _ = nb
            B = sum(n.astype(int) for n in nb)
            A = sum(((nb[i] == 0) & (nb[(i + 1) % 8] == 1)).astype(int) for i in range(8))
            c = (P2 * P4 * P6 == 0) & (P4 * P6 * P8 == 0) if langkah == 0 else (P2 * P4 * P8 == 0) & (P2 * P6 * P8 == 0)
            hapus = (img == 1) & (B >= 2) & (B <= 6) & (A == 1) & c
            if hapus.any():
                img[hapus] = 0
                ubah = True
        if not ubah:
            return img.astype(bool)


def lacak(sk):
    # piksel skeleton -> daftar garis berurutan, dimulai dari ujung (tetangga paling sedikit)
    titik = set(zip(*np.nonzero(sk)))
    tetangga = lambda q: [(q[0] + a, q[1] + b) for a in (-1, 0, 1) for b in (-1, 0, 1)
                          if (a or b) and (q[0] + a, q[1] + b) in titik]
    dikunjungi, hasil = set(), []
    for awal in sorted(titik, key=lambda q: len(tetangga(q))):
        if awal in dikunjungi:
            continue
        jalur, q = [awal], awal
        dikunjungi.add(awal)
        while True:
            calon = [t for t in tetangga(q) if t not in dikunjungi]
            if not calon:
                break
            q = calon[0]
            dikunjungi.add(q)
            jalur.append(q)
        if len(jalur) >= MIN_PIKSEL:
            hasil.append([((c + 0.5) / N * 100, (r + 0.5) / N * 100) for r, c in jalur])
    return hasil


def halus(garis, k=5):
    if len(garis) <= 2 * k:
        return garis
    xs = np.convolve([p[0] for p in garis], np.ones(k) / k, 'same')
    ys = np.convolve([p[1] for p in garis], np.ones(k) / k, 'same')
    return list(zip(xs, ys))[k // 2: len(garis) - k // 2]


def sampel(garis, jarak):
    panjang = [0.0]
    for a, b in zip(garis, garis[1:]):
        panjang.append(panjang[-1] + math.dist(a, b))
    if panjang[-1] == 0:
        return garis[:1]
    hasil, i = [], 0
    for n in range(int(panjang[-1] // jarak) + 1):
        s = n * jarak
        while panjang[i + 1] < s:
            i += 1
        f = (s - panjang[i]) / (panjang[i + 1] - panjang[i]) if panjang[i + 1] > panjang[i] else 0
        a, b = garis[i], garis[i + 1]
        hasil.append((a[0] + (b[0] - a[0]) * f, a[1] + (b[1] - a[1]) * f))
    return hasil


def main(path):
    data = json.load(open(path, encoding='utf-8'))
    for a in data['aksara']:
        garis = [sampel(halus(g), JARAK) for g in lacak(zhang_suen(raster(a['goresan'])))]
        a['tengah'] = [[{'x': round(x, 2), 'y': round(y, 2)} for x, y in g] for g in garis if len(g) >= 2]
    data['versi'] = 3
    json.dump(data, open(path, 'w', encoding='utf-8'), ensure_ascii=False, separators=(',', ':'))
    print('Garis tengah ditambahkan untuk', len(data['aksara']), 'aksara.')


if __name__ == '__main__':
    main(sys.argv[1])
