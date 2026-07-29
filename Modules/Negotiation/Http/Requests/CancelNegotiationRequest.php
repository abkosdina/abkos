<?php

namespace Modules\Negotiation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CancelNegotiationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [];
    }
}
