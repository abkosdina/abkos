<?php

namespace Modules\KYC\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RequestKycCorrectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermissionTo('kyc.request_correction') ?? false;
    }

    public function rules(): array
    {
        return [
            'reason' => ['required', 'string', 'max:500'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'reason.required' => 'Correction reason is required.',
            'reason.string' => 'Correction reason must be a string.',
            'reason.max' => 'Correction reason must not exceed 500 characters.',
            'comment.string' => 'Comment must be a string.',
            'comment.max' => 'Comment must not exceed 1000 characters.',
        ];
    }
}
