<?php

namespace App\Http\Requests;

use App\Enums\Peran;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** verifikasiFormatData: seluruh galat per isian di-return sekaligus. */
class DaftarRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'email'    => is_string($this->input('email')) ? trim($this->input('email')) : $this->input('email'),
            'username' => is_string($this->input('username')) ? trim($this->input('username')) : $this->input('username'),
        ]);
    }

    public function rules(): array
    {
        return [
            // Tim Materi tidak dibuat dari layar ini (I-01-01)
            'peran'           => ['required', Rule::in([Peran::Pelajar->value, Peran::Pengajar->value])],
            'email'           => ['required', 'string', 'email', 'max:255'],
            'username'        => ['required', 'string', 'regex:/^[A-Za-z0-9_]{3,30}$/'],
            'password'        => ['required', 'string', 'min:8', 'regex:/\p{L}/u', 'regex:/\d/'],
            'setuju_tnc' => ['accepted'],
        ];
    }

    public function messages(): array
    {
        $pesan = [
            'peran.required'           => 'Pilih jenis akun Pelajar atau Pengajar',
            'peran.in'                 => 'Pilih jenis akun Pelajar atau Pengajar',
            'setuju_tnc.accepted' => 'Terms & Conditions belum disetujui',
        ];

        foreach (['required', 'string', 'email', 'max'] as $aturan) {
            $pesan["email.$aturan"] = 'Format email tidak valid';
        }
        foreach (['required', 'string', 'regex'] as $aturan) {
            $pesan["username.$aturan"] = 'Username 3–30 karakter: huruf, angka, atau garis bawah';
        }
        foreach (['required', 'string', 'min', 'regex'] as $aturan) {
            $pesan["password.$aturan"] = 'Kata sandi minimal 8 karakter dan memuat huruf serta angka';
        }

        return $pesan;
    }
}