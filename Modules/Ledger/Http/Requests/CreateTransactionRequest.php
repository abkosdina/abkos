<?php

namespace Modules\Ledger\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', 'string', 'in:deposit,withdraw,escrow_lock,escrow_release,commission,refund,penalty,transfer,adjustment,settlement'],
            'description' => ['required', 'string', 'max:1024'],
            'metadata' => ['sometimes', 'array'],
            'entries' => ['required', 'array', 'min:2'],
            'entries.*.account_id' => ['required', 'integer', 'exists:accounts,id'],
            'entries.*.debit' => ['required', 'numeric', 'min:0'],
            'entries.*.credit' => ['required', 'numeric', 'min:0'],
            'entries.*.description' => ['sometimes', 'string', 'max:1024'],
            'entries.*.metadata' => ['sometimes', 'array'],
        ];
    }
}
