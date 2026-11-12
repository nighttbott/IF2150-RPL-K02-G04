<?php

namespace App\Http\Requests;

use App\Enums\ModeLatihan;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** Validasi form I-15 Terbitkan Tugas. Baris komponen yang materinya kosong diabaikan. */
class SimpanTugasRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // hak akses pemilik kelas diperiksa controller lewat KelasBelajarPolicy
    }

    protected function prepareForValidation(): void
    {
        $baris = collect($this->input('komponen', []))
            ->filter(fn ($k) => is_array($k) && filled($k['id_materi'] ?? null))
            ->values()
            ->all();

        $this->merge(['komponen' => $baris]);
    }

    public function rules(): array
    {
        return [
            'instruksi' => ['required', 'string', 'max:2000'],
            'tenggat' => ['required', 'date', 'after:now'],
            'aksi' => ['required', Rule::in(['draf', 'terbitkan'])],
            'komponen' => ['required', 'array', 'min:1'],
            'komponen.*.jenis' => ['required', Rule::in(['materi', 'latihan'])],
            'komponen.*.id_materi' => ['required', 'integer', 'exists:materi_aksara,id_materi'],
            'komponen.*.mode' => ['nullable', Rule::in(ModeLatihan::nilai())],
            'komponen.*.wajib' => ['nullable', 'boolean'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            foreach ($this->input('komponen', []) as $i => $k) {
                if (($k['jenis'] ?? null) === 'latihan' && blank($k['mode'] ?? null)) {
                    $v->errors()->add("komponen.$i.mode", 'Pilih mode latihan untuk komponen latihan.');
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'instruksi.required' => 'Instruksi tugas wajib diisi.',
            'tenggat.required' => 'Tenggat wajib diisi.',
            'tenggat.after' => 'Tenggat harus lebih akhir dari waktu sekarang.',
            'komponen.required' => 'Tambahkan minimal satu komponen tugas.',
            'komponen.min' => 'Tambahkan minimal satu komponen tugas.',
            'komponen.*.id_materi.exists' => 'Materi pada komponen tidak ditemukan.',
        ];
    }
}
