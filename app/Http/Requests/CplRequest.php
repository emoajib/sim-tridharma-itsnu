<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CplRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'kode_cpl' => 'required|string',
            'prodi_id' => 'required|exists:m_prodi,id',
            'deskripsi' => 'required|string',
            'jenis' => 'required|string',
        ];
    }
}
