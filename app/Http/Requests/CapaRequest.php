<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CapaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->can('capa.manage');
    }

    public function rules(): array
    {
        $rules = [
            'root_cause_category' => 'nullable|string|max:100',
            'root_cause_analysis' => 'nullable|string',
            'corrective_action' => 'nullable|string',
            'corrective_deadline' => 'nullable|date',
            'corrective_evidence_file' => 'nullable|file|mimes:pdf,doc,docx,xlsx,jpg,png|max:10240',
            'preventive_action' => 'nullable|string',
            'preventive_deadline' => 'nullable|date',
            'preventive_evidence_file' => 'nullable|file|mimes:pdf,doc,docx,xlsx,jpg,png|max:10240',
            'status' => 'nullable|string|in:draft,open,in_progress,awaiting_verification,verified,closed,archived,rejected',
            'verification_note' => 'nullable|string',
        ];

        return $rules;
    }

    public function messages(): array
    {
        return [
            'corrective_evidence_file.mimes' => 'File bukti korektif harus berupa: pdf, doc, docx, xlsx, jpg, png.',
            'corrective_evidence_file.max' => 'File bukti korektif maksimal 10 MB.',
            'preventive_evidence_file.mimes' => 'File bukti preventif harus berupa: pdf, doc, docx, xlsx, jpg, png.',
            'preventive_evidence_file.max' => 'File bukti preventif maksimal 10 MB.',
            'status.in' => 'Status CAPA tidak valid.',
        ];
    }
}
