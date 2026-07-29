<?php

namespace Modules\Advertisements\Http\Requests;

class PublishAdvertisementRequest extends AdvertisementWorkflowRequest
{
    public function rules(): array
    {
        return $this->workflowRules();
    }
}
