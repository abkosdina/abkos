<?php

namespace Modules\Ledger\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'account_type_id' => ['required', 'integer', 'exists:account_types,id'],
            'owner_type' => ['required', 'string', 'max:255'],
            'owner_id' => ['required', 'integer'],
            'name' => ['required', 'string', 'max:255'],
            'currency' => ['required', 'string', 'max:10'],
            'metadata' => ['sometimes', 'array'],
            'status' => ['sometimes', 'string', 'in:active,inactive,suspended,closed'],
        ];
    }
}
