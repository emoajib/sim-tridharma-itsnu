<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RkatRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->can('rkat.create');
    }

    public function rules(): array
    {
        $routeName = $this->route()?->getName();

        // Pagu store route
        if ($routeName && str_contains($routeName, 'pagu')) {
            return [
                'periode_id' => 'required|exists:m_periode_akademik,id',
                'unit_type' => 'required|in:Rektorat,Fakultas,Prodi',
                'unit_id' => 'required|integer',
                'pagu_total' => 'required|numeric|min:0',
            ];
        }

        // Check pagu availability route
        if ($routeName && str_contains($routeName, 'check-pagu')) {
            return [
                'prodi_id' => 'required|exists:m_prodi,id',
                'periode_id' => 'required|exists:m_periode_akademik,id',
                'amount' => 'required|numeric|min:0',
            ];
        }

        // Default: general RKAT item validation
        return [
            'prodi_id' => 'required|exists:m_prodi,id',
            'periode_id' => 'required|exists:m_periode_akademik,id',
            'kode_rekening' => 'required|string|max:50',
            'uraian' => 'required|string|min:10',
            'volume' => 'required|integer|min:1',
            'satuan' => 'required|string|max:50',
            'harga_satuan' => 'required|numeric|min:0',
            'sub_total' => 'required|numeric|min:0',
            'jenis_anggaran' => 'required|in:belanja_pegawai,belanja_barang,belanja_modal',
            'status' => 'nullable|in:draft,submitted,approved,rejected',
        ];
    }

    public function messages(): array
    {
        return [
            'prodi_id.required' => 'Program studi wajib dipilih.',
            'periode_id.required' => 'Periode akademik wajib dipilih.',
            'kode_rekening.required' => 'Kode rekening wajib diisi.',
            'uraian.required' => 'Uraian kegiatan wajib diisi.',
            'uraian.min' => 'Uraian kegiatan minimal 10 karakter.',
            'volume.required' => 'Volume wajib diisi.',
            'volume.min' => 'Volume minimal 1.',
            'harga_satuan.required' => 'Harga satuan wajib diisi.',
            'sub_total.required' => 'Sub total wajib diisi.',
            'jenis_anggaran.required' => 'Jenis anggaran wajib dipilih.',
            'jenis_anggaran.in' => 'Jenis anggaran tidak valid.',
            'unit_type.required' => 'Tipe unit wajib dipilih.',
            'unit_type.in' => 'Tipe unit tidak valid.',
            'unit_id.required' => 'Unit wajib dipilih.',
            'pagu_total.required' => 'Total pagu wajib diisi.',
            'pagu_total.min' => 'Total pagu tidak boleh kurang dari 0.',
            'amount.required' => 'Jumlah wajib diisi.',
            'amount.min' => 'Jumlah tidak boleh kurang dari 0.',
        ];
    }
}
