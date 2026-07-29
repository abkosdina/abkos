<?php

namespace Modules\Wallet\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class WalletTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'amount' => 'required|numeric|min:0.00000001',
            'description' => 'sometimes|string|max:1000',
            'metadata' => 'sometimes|array',
            'destination_wallet_id' => 'sometimes|exists:wallets,id',
            'lock_id' => 'sometimes|exists:wallet_locks,id',
            'reason' => 'sometimes|string|max:255',
            'notes' => 'sometimes|string|max:1000',
            'expires_at' => 'sometimes|date',
        ];
    }
}
