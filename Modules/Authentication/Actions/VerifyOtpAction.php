<?php

namespace Modules\Authentication\Actions;

use Modules\Authentication\Services\OtpService;

class VerifyOtpAction
{
    public function __construct(protected OtpService $service)
    {
    }

    public function execute(string $mobile, string $otp, string $ipAddress, ?string $userAgent = null): array
    {
        return $this->service->verifyOtp($mobile, $otp, $ipAddress, $userAgent);
    }
}
