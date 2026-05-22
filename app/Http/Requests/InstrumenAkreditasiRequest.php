<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InstrumenAkreditasiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'lembaga_id' => 'required|exists:m_lembaga_akreditasi,id',
            'nama_instrumen' => 'required|string|max:100',
            'matriks_kriteria' => 'nullable|array',
        ];
    }

    public function messages(): array
    {
        return [
            'lembaga_id.required' => 'Lembaga akreditasi harus dipilih.',
            'lembaga_id.exists' => 'Lembaga akreditasi tidak valid.',
            'nama_instrumen.required' => 'Nama instrumen harus diisi.',
            'nama_instrumen.max' => 'Nama instrumen maksimal 100 karakter.',
        ];
    }
}
