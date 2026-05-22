<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LembagaAkreditasiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('lembagaAkreditasi')?->id;

        return [
            'nama_lembaga' => 'required|string|max:100',
            'singkatan' => 'required|string|max:20|unique:m_lembaga_akreditasi,singkatan,'.$id,
            'deskripsi' => 'nullable|string',
        ];
    }
}
