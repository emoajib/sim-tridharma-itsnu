<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FasilitasInternetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'periode_id' => 'required|exists:m_periode_akademik,id',
            'bandwidth_total_mbps' => 'required|integer|min:1',
            'jumlah_mahasiswa_aktif' => 'required|integer|min:1',
            'jumlah_titik_hotspot' => 'required|integer|min:0',
        ];
    }
}
