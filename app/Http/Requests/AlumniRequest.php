<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AlumniRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nim' => 'required|string|max:50',
            'nama' => 'required|string|max:255',
            'prodi_id' => 'required|exists:m_prodi,id',
            'tahun_lulus' => 'required|string|max:4',
            'masa_tunggu' => 'nullable|integer|min:0',
            'gaji_pertama' => 'nullable|numeric|min:0',
            'pekerjaan' => 'nullable|string|max:255',
        ];
    }
}
