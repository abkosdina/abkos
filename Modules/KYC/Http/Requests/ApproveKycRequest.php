<?php

namespace Modules\KYC\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ApproveKycRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermissionTo('kyc.approve') ?? false;
    }

    public function rules(): array
    {
        return [
            'comment' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'comment.string' => 'Comment must be a string.',
            'comment.max' => 'Comment must not exceed 1000 characters.',
        ];
    }
}
