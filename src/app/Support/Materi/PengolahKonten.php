<?php

namespace App\Support\Materi;

use App\Enums\JenisAksara;

/** Fungsi murni untuk memecah/menyusun teks konten materi (mudah diuji tanpa basis data). */
final class PengolahKonten
{
    /** Teks form "Aturan penulisan dan contoh" dipecah pada kata "Contoh:". @return array{aturan: string, contoh: string} */
    public static function pisahAturanContoh(string $teks): array
    {
        if (preg_match('/^(.*?)\bContoh\s*:\s*(.*)$/isu', trim($teks), $m)) {
            return ['aturan' => trim($m[1]), 'contoh' => trim($m[2])];
        }

        return ['aturan' => trim($teks), 'contoh' => ''];
    }

    /** Gabungan sebaliknya, untuk mengisi ulang form sunting. */
    public static function gabungAturanContoh(string $aturan, string $contoh): string
    {
        return trim($aturan)."\nContoh: ".trim($contoh);
    }

    /** Nama materi berbentuk "Nglegena: ma ga ba tha nga" -> "ꦩ ꦒ ꦧ ꦛ ꦔ". Mengembalikan string kosong bila ada kode yang tidak dikenal atau tidak ada bagian setelah ':'. */
    public static function bentukDariNama(JenisAksara $jenis, string $nama): string
    {
        $bagian = explode(':', $nama, 2);
        if (count($bagian) < 2) {
            return '';
        }
        $hasil = [];
        foreach (preg_split('/\s+/u', trim($bagian[1]), -1, PREG_SPLIT_NO_EMPTY) as $kode) {
            $karakter = TemplateAksara::karakterDariKode($jenis, $kode);
            if ($karakter === null) {
                return '';
            }
            $hasil[] = $karakter;
        }

        return implode(' ', $hasil);
    }

    /** @return list<string> */
    public static function pecahBentuk(string $bentuk): array
    {
        return preg_split('/\s+/u', trim($bentuk), -1, PREG_SPLIT_NO_EMPTY) ?: [];
    }

    /** Contoh `ꦲꦤ = "ana" (ada); ꦕꦫ = "cara"` -> [['kata' => 'ꦲꦤ', 'bunyi' => 'ana'], ...] @return list<array{kata: string, bunyi: string}> */
    public static function pasangContoh(string $contoh): array
    {
        preg_match_all('/(\S+)\s*=\s*"([^"]+)"/u', $contoh, $m, PREG_SET_ORDER);

        return array_map(fn ($p) => ['kata' => $p[1], 'bunyi' => $p[2]], $m);
    }

    /** "1.0" -> "1.1", "1.9" -> "1.10", "2" -> "2.1". */
    public static function naikkanMinor(string $versi): string
    {
        $bagian = explode('.', $versi);
        $mayor = $bagian[0] !== '' ? $bagian[0] : '1';
        $minor = (int) ($bagian[1] ?? 0);

        return $mayor.'.'.($minor + 1);
    }

    /** Pilih versi konten terbaru yang kompatibel dengan versi aplikasi (Algo-056). @param  list<array{versi_konten: string, versi_aplikasi_minimum: string}>  $kandidat */
    public static function pilihVersi(array $kandidat, string $versiAplikasi): ?array
    {
        usort($kandidat, fn ($a, $b) => version_compare($b['versi_konten'], $a['versi_konten']));
        foreach ($kandidat as $k) {
            if (version_compare($k['versi_aplikasi_minimum'], $versiAplikasi, '<=')) {
                return $k;
            }
        }

        return null;
    }
}
