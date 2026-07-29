<?php

namespace Modules\KYC\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RejectKycRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'reason' => ['required', 'string', 'max:255'],
            'comment' => ['nullable', 'string'],
            'required_corrections' => ['nullable', 'array'],
            'required_corrections.*' => ['string', 'max:255'],
        ];
    }
}
