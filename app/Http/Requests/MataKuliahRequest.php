<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

use Illuminate\Validation\Rule;

class MataKuliahRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('mata_kuliah')?->id;

        return [
            'kode_mk' => [
                'required',
                'string',
                Rule::unique('m_mata_kuliah', 'kode_mk')->ignore($id)
            ],
            'nama_mk' => 'required|string',
            'prodi_id' => 'required|exists:m_prodi,id',
        ];
    }
}
