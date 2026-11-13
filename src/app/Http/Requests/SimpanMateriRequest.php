<?php

namespace App\Http\Requests;

use App\Enums\JenisAksara;
use App\Enums\StatusPublikasi;
use App\Models\MateriAksara;
use App\Support\Materi\DataKonten;
use App\Support\Materi\PengolahKonten;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** Validasi form I-19 (Algo-041); format dan ukuran berkas juga diperiksa ulang di MateriController::validasiFormatFile. */
class SimpanMateriRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // hak akses diperiksa di MateriController (verifikasiHakAkses)
    }

    public function rules(): array
    {
        return [
            'nama_materi' => ['required', 'string', 'max:100'],
            'jenis_aksara' => ['required', Rule::enum(JenisAksara::class)],
            'tingkat_kesulitan' => ['required', 'integer', 'between:1,3'],
            'aturan_contoh' => ['required', 'string', 'max:5000'],
            'berkas' => ['nullable', 'file', 'extensions:json,mp3,png,svg', 'max:10240'],
        ];
    }

    public function messages(): array
    {
        return [
            'nama_materi.required' => 'Nama materi wajib diisi.',
            'nama_materi.max' => 'Nama materi maksimal 100 karakter.',
            'jenis_aksara.required' => 'Pilih jenis aksara.',
            'tingkat_kesulitan.required' => 'Pilih tingkat kesulitan.',
            'aturan_contoh.required' => 'Aturan penulisan dan contoh wajib diisi.',
            'berkas.extensions' => 'Jenis berkas tidak didukung',
            'berkas.max' => 'Ukuran berkas melebihi 10 MB',
        ];
    }

    public function keDataKonten(StatusPublikasi $status = StatusPublikasi::Draf): DataKonten
    {
        $jenis = JenisAksara::from($this->input('jenis_aksara'));
        $teks = PengolahKonten::pisahAturanContoh((string) $this->input('aturan_contoh'));

        return new DataKonten(
            namaMateri: trim((string) $this->input('nama_materi')),
            jenisAksara: $jenis,
            tingkatKesulitan: (int) $this->input('tingkat_kesulitan'),
            statusPublikasi: $status,
            bentukAksara: $this->bentukAksara($jenis),
            aturan: $teks['aturan'],
            contoh: $teks['contoh'],
            versiAplikasiMinimum: (string) config('ngaksara.versi_aplikasi'),
            berkasBaru: $this->file('berkas'),
        );
    }

    /** Daftar aksara dari nama materi; bila nama tanpa ":" (mis. Sandhangan swara), pakai bentuk konten yang sudah ada. */
    private function bentukAksara(JenisAksara $jenis): string
    {
        $bentuk = PengolahKonten::bentukDariNama($jenis, (string) $this->input('nama_materi'));
        $idMateri = $this->route('materi');
        if ($bentuk === '' && $idMateri !== null) {
            $bentuk = (string) MateriAksara::with('konten')->find((int) $idMateri)?->konten?->bentuk_aksara;
        }

        return $bentuk;
    }
}
