<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AgentRunRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'prodi_id' => 'nullable|integer|exists:m_prodi,id',
            'fakultas_id' => 'nullable|integer|exists:m_fakultas,id',
            'periode_id' => 'nullable|integer|exists:m_periode_akademik,id',
            'periode' => 'nullable|string|max:20',
            'filter' => 'nullable|array',
            'options' => 'nullable|array',
        ];
    }

    public function messages()
    {
        return [
            'prodi_id.exists' => 'Program studi tidak ditemukan.',
            'fakultas_id.exists' => 'Fakultas tidak ditemukan.',
            'periode_id.exists' => 'Periode akademik tidak ditemukan.',
            'periode.max' => 'Periode maksimal 20 karakter.',
        ];
    }
}
