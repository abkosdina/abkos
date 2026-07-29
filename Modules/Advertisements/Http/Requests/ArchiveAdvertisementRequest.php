<?php

namespace Modules\Advertisements\Http\Requests;

class ArchiveAdvertisementRequest extends AdvertisementWorkflowRequest
{
    public function rules(): array
    {
        return $this->workflowRules();
    }
}
