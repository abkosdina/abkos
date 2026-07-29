<?php

namespace Modules\Advertisements\Http\Requests;

class RejectAdvertisementRequest extends AdvertisementWorkflowRequest
{
    public function rules(): array
    {
        return $this->workflowRules([
            'reason' => ['required', 'string', 'max:500'],
            'description' => ['required', 'string', 'max:2000'],
            'attachments' => ['nullable', 'array'],
        ]);
    }
}
