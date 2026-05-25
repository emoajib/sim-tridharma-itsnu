<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

use Illuminate\Validation\Rule;

class FakultasRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('fakultas')?->id;

        return [
            'kode_fakultas' => [
                'required', 
                'string', 
                Rule::unique('m_fakultas', 'kode_fakultas')->ignore($id)
            ],
            'nama_fakultas' => 'required|string',
            'alamat' => 'nullable|string',
            'telepon' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:100',
        ];
    }
}
