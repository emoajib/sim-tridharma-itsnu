<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class KuisionerTracerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'prodi_id' => 'required|exists:m_prodi,id',
            'judul_kuisioner' => 'required|string|max:255',
            'tahun' => 'required|string|max:4',
            'pertanyaan' => 'required|json',
        ];
    }
}
