<?php

namespace Modules\Advertisements\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

abstract class AdvertisementWorkflowRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function workflowRules(array $overrides = []): array
    {
        $defaults = [
            'reason' => ['nullable', 'string', 'max:500'],
            'comment' => ['nullable', 'string', 'max:1000'],
            'metadata' => ['nullable', 'array'],
        ];

        return array_replace_recursive($defaults, $overrides);
    }
}
