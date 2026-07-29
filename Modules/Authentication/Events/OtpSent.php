<?php

namespace Modules\Authentication\Events;

class OtpSent
{
    public function __construct(public string $mobile)
    {
    }
}
