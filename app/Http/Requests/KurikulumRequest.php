<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class KurikulumRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nama_kurikulum' => 'required|string',
            'prodi_id' => 'required|exists:m_prodi,id',
            'tahun_berlaku' => 'required|string',
        ];
    }
}
