<?php

namespace Modules\Advertisements\Http\Requests;

class SubmitAdvertisementRequest extends AdvertisementWorkflowRequest
{
    public function rules(): array
    {
        return $this->workflowRules();
    }
}
