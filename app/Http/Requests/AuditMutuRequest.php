<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

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
            'judul_audit' => 'required|string|max:200',
            'tanggal_audit' => 'required|date',
            'auditor' => 'nullable|string|max:200',
            'temuan' => 'nullable|string',
            'rekomendasi' => 'nullable|string',
            'standar_mutu_id' => 'nullable|exists:m_standar_mutu,id',
            'severity' => 'nullable|in:ringan,sedang,berat,kritis',
            'pic_user_id' => 'nullable|exists:users,id',
            'auditor_user_id' => 'nullable|exists:users,id',
            'deadline_tindak_lanjut' => 'nullable|date',
            'evidence_file' => 'nullable|file|mimes:pdf,doc,docx,xlsx,jpg,png|max:10240',
        ];

        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            $rules['tindak_lanjut'] = 'nullable|string';
            $rules['status'] = 'required|string|in:draft,submitted,assigned,in_progress,awaiting_verification,verified,closed,archived,rejected';
        }

        return $rules;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('temuan')) {
            $this->merge([
                'temuan' => strip_tags($this->input('temuan')),
            ]);
        }

        if ($this->has('rekomendasi')) {
            $this->merge([
                'rekomendasi' => strip_tags($this->input('rekomendasi')),
            ]);
        }
    }

    public function messages(): array
    {
        return [
            'prodi_id.required' => 'Program studi wajib dipilih.',
            'prodi_id.exists' => 'Program studi tidak valid.',
            'periode_id.required' => 'Periode akademik wajib dipilih.',
            'periode_id.exists' => 'Periode akademik tidak valid.',
            'judul_audit.required' => 'Judul audit wajib diisi.',
            'judul_audit.max' => 'Judul audit maksimal 200 karakter.',
            'tanggal_audit.required' => 'Tanggal audit wajib diisi.',
            'tanggal_audit.date' => 'Format tanggal tidak valid.',
            'severity.in' => 'Tingkat keparahan tidak valid.',
            'evidence_file.mimes' => 'File bukti harus berupa: pdf, doc, docx, xlsx, jpg, png.',
            'evidence_file.max' => 'File bukti maksimal 10 MB.',
        ];
    }
}
