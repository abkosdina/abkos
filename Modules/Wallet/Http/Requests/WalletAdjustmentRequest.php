<?php

namespace Modules\Wallet\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class WalletAdjustmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'wallet_id' => 'required|exists:wallets,id',
            'amount' => 'required|numeric|min:0.00000001',
            'transaction_type' => 'required|string|in:adjustment,commission,penalty,bonus',
            'direction' => 'required|string|in:credit,debit',
            'reason' => 'required|string|max:1000',
            'description' => 'sometimes|string|max:1000',
            'idempotency_key' => 'required|string|max:100',
            'metadata' => 'sometimes|array',
            'reference_type' => 'sometimes|string|max:100',
            'reference_id' => 'sometimes|numeric',
        ];
    }
}
