<?php

namespace App\Http\Requests\Latihan;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class KirimJawabanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'idSoal' => ['required', 'string', 'max:64'],
            'goresan' => ['required', 'array', 'min:1', 'max:'.config('latihan.maks_goresan')],
            'goresan.*' => ['required', 'array', 'min:1', 'max:'.config('latihan.maks_titik_per_goresan')],
        ];
    }

    /** Titik diperiksa dalam satu loop, bukan aturan wildcard per titik, karena payload bisa memuat ribuan titik. */
    public function after(): array
    {
        return [function (Validator $validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }
            $total = 0;
            foreach ($this->input('goresan') as $goresan) {
                foreach ($goresan as $titik) {
                    $total++;
                    if (! $this->titikSah($titik)) {
                        $validator->errors()->add('goresan', 'Data goresan tidak valid.');

                        return;
                    }
                }
            }
            if ($total > config('latihan.maks_titik_total')) {
                $validator->errors()->add('goresan', 'Goresan terlalu panjang.');
            }
        }];
    }

    private function titikSah(mixed $titik): bool
    {
        // Titik boleh sedikit di luar kanvas karena setPointerCapture tetap merekam di luar tepi.
        $dalamRentang = fn ($v) => is_int($v) || is_float($v) ? $v >= -0.5 && $v <= 3.0 : false;

        return is_array($titik)
            && $dalamRentang($titik['x'] ?? null)
            && $dalamRentang($titik['y'] ?? null)
            && (! isset($titik['t']) || is_int($titik['t']) || is_float($titik['t']));
    }
}
