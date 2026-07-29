<?php

namespace Modules\Authentication\Services;

use Modules\Authentication\Providers\Sms\SmsServiceInterface;

class SmsService
{
    public function __construct(protected SmsServiceInterface $provider)
    {
    }

    public function send(string $mobile, string $message): bool
    {
        return $this->provider->send($mobile, $message);
    }
}
