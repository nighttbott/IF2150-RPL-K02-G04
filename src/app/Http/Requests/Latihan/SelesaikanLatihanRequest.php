<?php

namespace App\Http\Requests\Latihan;

use Illuminate\Foundation\Http\FormRequest;

class SelesaikanLatihanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return ['idSoal' => ['required', 'string', 'max:64']];
    }
}
