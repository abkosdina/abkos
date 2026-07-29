<?php

namespace Modules\Negotiation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreNegotiationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'advertisement_id' => ['required', 'integer', 'exists:advertisements,id'],
            'seller_id' => ['required', 'integer', 'exists:users,id'],
            'conversation_id' => ['nullable', 'integer'],
        ];
    }
}
