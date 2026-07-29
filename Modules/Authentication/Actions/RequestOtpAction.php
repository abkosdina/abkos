<?php

namespace Modules\Authentication\Actions;

use Modules\Authentication\Services\OtpService;

class RequestOtpAction
{
    public function __construct(protected OtpService $service)
    {
    }

    public function execute(string $mobile, string $ipAddress, ?string $userAgent = null): array
    {
        return $this->service->requestOtp($mobile, $ipAddress, $userAgent);
    }
}
