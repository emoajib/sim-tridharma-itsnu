<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DosenRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $dosenId = $this->route('dosen')?->id;

        return [
            'nidn' => 'required|string|unique:m_dosen,nidn,'.($dosenId ?? 'NULL').',id',
            'nama_depan' => 'required|string',
            'nama_belakang' => 'nullable|string',
            'prodi_id' => 'required|exists:m_prodi,id',
        ];
    }

    public function messages(): array
    {
        return [
            'nidn.required' => 'NIDN harus diisi.',
            'nidn.unique' => 'NIDN sudah terdaftar.',
            'nama_depan.required' => 'Nama depan harus diisi.',
            'prodi_id.required' => 'Program studi harus dipilih.',
            'prodi_id.exists' => 'Program studi tidak valid.',
        ];
    }
}
