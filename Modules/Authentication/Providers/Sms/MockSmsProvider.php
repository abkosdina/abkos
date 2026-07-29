<?php

namespace Modules\Authentication\Providers\Sms;

class MockSmsProvider implements SmsServiceInterface
{
    public function send(string $mobile, string $message): bool
    {
        return true;
    }
}
