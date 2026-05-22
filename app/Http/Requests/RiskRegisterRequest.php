<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RiskRegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'prodi_id' => 'required|exists:m_prodi,id',
            'periode_id' => 'required|exists:m_periode_akademik,id',
            'nama_risiko' => 'required|string',
            'kategori' => 'nullable|string',
            'dampak' => 'required|string|in:rendah,sedang,tinggi',
            'probabilitas' => 'required|string|in:rendah,sedang,tinggi',
            'skor_risiko' => 'required|string|max:20',
            'mitigasi' => 'nullable|string',
            'penanggung_jawab' => 'nullable|string',
        ];

        if ($this->isMethod('PUT')) {
            $rules['status'] = 'required|string|in:open,in_progress,closed';
        }

        return $rules;
    }
}
