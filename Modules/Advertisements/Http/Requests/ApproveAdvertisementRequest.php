<?php

namespace Modules\Advertisements\Http\Requests;

class ApproveAdvertisementRequest extends AdvertisementWorkflowRequest
{
    public function rules(): array
    {
        return $this->workflowRules([
            'reason' => ['required', 'string', 'max:500'],
            'attachments' => ['nullable', 'array'],
        ]);
    }
}
