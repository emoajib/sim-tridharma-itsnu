<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class IkuRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->can('iku.create');
    }

    public function rules(): array
    {
        $ikuId = $this->route('iku')?->id;

        return [
            'kode_iku' => 'required|string|max:50|unique:m_indikator_iku,kode_iku,' . $ikuId,
            'nama_indikator' => 'required|string|max:200',
            'deskripsi' => 'nullable|string',
            'lembaga_id' => 'nullable|exists:m_lembaga_akreditasi,id',
            'bobot' => 'nullable|numeric|min:0|max:100',
            'target' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'kode_iku.required' => 'Kode IKU wajib diisi.',
            'kode_iku.unique' => 'Kode IKU sudah digunakan.',
            'nama_indikator.required' => 'Nama indikator wajib diisi.',
            'bobot.min' => 'Bobot tidak boleh kurang dari 0.',
            'bobot.max' => 'Bobot tidak boleh lebih dari 100.',
            'target.min' => 'Target tidak boleh kurang dari 0.',
        ];
    }
}
