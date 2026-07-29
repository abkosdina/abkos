<?php

namespace Modules\Authentication\Providers\Sms;

interface SmsServiceInterface
{
    public function send(string $mobile, string $message): bool;
}
