<?php

namespace App\Http\Requests\Sinta;

use Illuminate\Foundation\Http\FormRequest;

class ImportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file' => 'required|file|mimes:xlsx,xls,csv,html,tsv|max:10240',
        ];
    }

    public function messages(): array
    {
        return [
            'file.required' => 'File export SINTA wajib diunggah.',
            'file.mimes' => 'Format file tidak didukung. Gunakan XLSX, XLS, CSV, HTML, atau TSV.',
            'file.max' => 'Ukuran file maksimal 10MB.',
        ];
    }
}
