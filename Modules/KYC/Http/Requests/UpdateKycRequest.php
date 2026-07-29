<?php

namespace Modules\KYC\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateKycRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'document_type' => ['nullable', 'string', 'max:100'],
            'requested_by' => ['nullable', 'integer', 'exists:users,id'],
            'deadline_at' => ['nullable', 'date'],
            'priority' => ['nullable', 'integer', 'min:1'],
            'status' => ['nullable', 'string', 'in:' . implode(',', config('kyc.statuses'))],
        ];
    }
}
