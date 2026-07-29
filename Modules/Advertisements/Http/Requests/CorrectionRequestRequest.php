<?php

namespace Modules\Advertisements\Http\Requests;

class CorrectionRequestRequest extends AdvertisementWorkflowRequest
{
    public function rules(): array
    {
        return $this->workflowRules([
            'reason' => ['required', 'string', 'max:500'],
            'description' => ['required', 'string', 'max:2000'],
            'fields_to_correct' => ['nullable', 'array'],
            'attachments' => ['nullable', 'array'],
        ]);
    }
}
