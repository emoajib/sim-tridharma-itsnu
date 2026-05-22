<?php

// Vetted by AI - Manual Review Required by Senior Engineer/Manager

namespace App\Http\Requests\Rkat;

use Illuminate\Foundation\Http\FormRequest;

class ApprovalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'action' => 'required|in:approve,reject,revise',
            'keterangan' => 'nullable|string',
        ];
    }
}
