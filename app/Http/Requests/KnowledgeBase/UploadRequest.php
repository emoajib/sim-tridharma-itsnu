<?php

namespace App\Http\Requests\KnowledgeBase;

use Illuminate\Foundation\Http\FormRequest;

class UploadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'judul' => 'required|string|max:255',
            'sumber' => 'nullable|string|max:255',
            'category_id' => 'nullable|exists:knowledge_base_categories,id',
        ];

        if ($this->isMethod('POST') && ! $this->route('knowledgeBaseDocument')) {
            $rules['file'] = 'required|file|mimes:pdf|max:51200';
        } else {
            $rules['file'] = 'nullable|file|mimes:pdf|max:51200';
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'file.required' => 'File PDF wajib diunggah.',
            'file.mimes' => 'File harus berformat PDF.',
            'file.max' => 'Ukuran file maksimal 50MB.',
            'judul.required' => 'Judul dokumen wajib diisi.',
            'category_id.exists' => 'Kategori tidak ditemukan.',
        ];
    }
}
