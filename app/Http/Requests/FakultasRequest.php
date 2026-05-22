<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

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
            'kode_fakultas' => 'required|string|unique:m_fakultas,kode_fakultas,'.$id,
            'nama_fakultas' => 'required|string',
            'alamat' => 'nullable|string',
            'telepon' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:100',
        ];
    }
}
