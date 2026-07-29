<?php

namespace Modules\Authentication\DTO;

class OtpRequestDTO
{
    public function __construct(
        public string $mobile,
        public string $ipAddress,
        public ?string $userAgent = null,
    ) {
    }
}
