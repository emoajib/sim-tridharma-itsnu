<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

use Illuminate\Validation\Rule;

class ProdiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $prodiId = $this->route('prodi')?->id;

        return [
            'kode_prodi' => [
                'required', 
                'string', 
                Rule::unique('m_prodi', 'kode_prodi')->ignore($prodiId)
            ],
            'nama_prodi' => 'required|string',
            'fakultas_id' => 'required|exists:m_fakultas,id',
            'lembaga_akreditasi_id' => 'nullable|exists:m_lembaga_akreditasi,id',
            'jenjang' => 'required|string',
            'akreditasi' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'kode_prodi.required' => 'Kode prodi harus diisi.',
            'kode_prodi.unique' => 'Kode prodi sudah terdaftar.',
            'nama_prodi.required' => 'Nama prodi harus diisi.',
            'fakultas_id.required' => 'Fakultas harus dipilih.',
            'fakultas_id.exists' => 'Fakultas tidak valid.',
            'jenjang.required' => 'Jenjang harus diisi.',
        ];
    }
}
