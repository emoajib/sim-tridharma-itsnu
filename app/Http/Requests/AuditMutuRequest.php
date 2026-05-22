<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AuditMutuRequest extends FormRequest
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
            'judul_audit' => 'required|string',
            'tanggal_audit' => 'required|date',
            'auditor' => 'nullable|string',
            'temuan' => 'nullable|string',
            'rekomendasi' => 'nullable|string',
        ];

        if ($this->isMethod('PUT')) {
            $rules['tindak_lanjut'] = 'nullable|string';
            $rules['status'] = 'required|string|in:open,in_progress,closed';
        }

        return $rules;
    }
}
