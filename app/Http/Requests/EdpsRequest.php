<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EdpsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->can('edps.manage');
    }

    public function rules(): array
    {
        $rules = [
            'target' => 'nullable|numeric|min:0|max:100',
            'capaian' => 'nullable|numeric|min:0|max:100',
            'analisis' => 'nullable|string',
            'bukti_file' => 'nullable|file|mimes:pdf,doc,docx,xlsx,jpg,png|max:10240',
            'status' => 'nullable|string|in:draft,completed',
        ];

        // On store (POST), require the relational fields
        if ($this->isMethod('POST')) {
            $rules['prodi_id'] = 'required|exists:m_prodi,id';
            $rules['periode_id'] = 'required|exists:m_periode_akademik,id';
            $rules['standar_mutu_id'] = 'required|exists:m_standar_mutu,id';
            $rules['target'] = 'required|numeric|min:0|max:100';
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'prodi_id.required' => 'Program studi wajib dipilih.',
            'periode_id.required' => 'Periode akademik wajib dipilih.',
            'standar_mutu_id.required' => 'Standar mutu wajib dipilih.',
            'target.min' => 'Target tidak boleh kurang dari 0.',
            'target.max' => 'Target tidak boleh lebih dari 100.',
            'capaian.min' => 'Capaian tidak boleh kurang dari 0.',
            'capaian.max' => 'Capaian tidak boleh lebih dari 100.',
            'bukti_file.mimes' => 'File bukti harus berupa: pdf, doc, docx, xlsx, jpg, png.',
            'bukti_file.max' => 'File bukti maksimal 10 MB.',
            'status.in' => 'Status harus draft atau completed.',
        ];
    }
}
