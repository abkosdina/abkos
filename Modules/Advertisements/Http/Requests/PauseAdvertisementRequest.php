<?php

namespace Modules\Advertisements\Http\Requests;

class PauseAdvertisementRequest extends AdvertisementWorkflowRequest
{
    public function rules(): array
    {
        return $this->workflowRules();
    }
}
