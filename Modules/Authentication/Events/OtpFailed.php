<?php

namespace Modules\Authentication\Events;

class OtpFailed
{
    public function __construct(public string $mobile)
    {
    }
}
