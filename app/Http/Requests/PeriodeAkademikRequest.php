<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PeriodeAkademikRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('periode_akademik')?->id;

        return [
            'kode_periode' => 'required|string|unique:m_periode_akademik,kode_periode,'.$id,
            'nama_periode' => 'required|string',
        ];
    }
}
