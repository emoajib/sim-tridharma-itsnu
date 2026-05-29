<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SeleksiPmbRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('seleksi_pmb')?->id;

        return [
            'periode_id' => 'required|exists:m_periode_akademik,id',
            'prodi_id' => 'required|exists:m_prodi,id',
            'pendaftar' => 'required|integer|min:0',
            'lulus_seleksi' => 'required|integer|min:0',
            'daftar_ulang' => 'required|integer|min:0',
            'maba_reguler' => 'required|integer|min:0',
            'maba_transfer' => 'required|integer|min:0',
            'maba_asing_ft' => 'required|integer|min:0',
            'maba_asing_pt' => 'required|integer|min:0',
        ];
    }
}
