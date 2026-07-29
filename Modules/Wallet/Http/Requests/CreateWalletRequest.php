<?php

namespace Modules\Wallet\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateWalletRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => 'required|exists:users,id',
            'ledger_account_id' => 'required|exists:accounts,id',
            'currency' => 'required|string|max:10',
            'status' => 'sometimes|string|in:active,suspended,frozen,closed',
            'metadata' => 'sometimes|array',
        ];
    }
}
