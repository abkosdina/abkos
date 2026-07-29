<?php

namespace Modules\Authentication\Events;

class OtpRequested
{
    public function __construct(public string $mobile)
    {
    }
}
