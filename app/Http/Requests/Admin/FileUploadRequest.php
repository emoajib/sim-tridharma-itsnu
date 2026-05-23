<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class FileUploadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $field = $this->has('logo') ? 'logo' : ($this->has('favicon') ? 'favicon' : 'file');
        
        $rules = [
            "{$field}" => 'required|image|mimes:png,jpg,jpeg,svg,webp,ico|max:2048',
        ];

        return $rules;
    }

    public function messages(): array
    {
        return [
            'favicon.required' => 'File favicon wajib diunggah.',
            'favicon.image' => 'File harus berupa gambar.',
            'favicon.mimes' => 'Format favicon: ico, png, svg.',
            'favicon.max' => 'Ukuran favicon maksimal 512KB.',
            'logo.required' => 'File logo wajib diunggah.',
            'logo.image' => 'File harus berupa gambar.',
            'logo.mimes' => 'Format logo: png, jpg, jpeg, svg, webp.',
            'logo.max' => 'Ukuran logo maksimal 2MB.',
        ];
    }
}
