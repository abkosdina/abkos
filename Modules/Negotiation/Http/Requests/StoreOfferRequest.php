<?php

namespace Modules\Negotiation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOfferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'min:1'],
            'description' => ['nullable', 'string'],
            'expires_at' => ['nullable', 'date'],
        ];
    }
}
