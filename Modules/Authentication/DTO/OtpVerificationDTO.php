<?php

namespace Modules\Authentication\DTO;

class OtpVerificationDTO
{
    public function __construct(
        public string $mobile,
        public string $otp,
        public string $ipAddress,
        public ?string $userAgent = null,
    ) {
    }
}
