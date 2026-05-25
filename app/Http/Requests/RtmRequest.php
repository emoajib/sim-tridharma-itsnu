<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RtmRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'judul' => 'required|string|max:200',
            'tanggal_rapat' => 'required|date',
            'agenda' => 'nullable|string',
            'notulen' => 'nullable|string',
            'file_notulen' => 'nullable|file|mimes:pdf,doc,docx|max:10240',
            'dipimpin_oleh_id' => 'nullable|exists:users,id',
            'status' => 'nullable|in:draft,conducted,cancelled',
        ];
    }
}
