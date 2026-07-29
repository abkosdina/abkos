<?php

namespace Modules\Authentication\Events;

class OtpVerified
{
    public function __construct(public string $mobile)
    {
    }
}
