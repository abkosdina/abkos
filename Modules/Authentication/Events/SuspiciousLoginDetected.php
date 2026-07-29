<?php

namespace Modules\Authentication\Events;

class SuspiciousLoginDetected
{
    public function __construct(public string $mobile)
    {
    }
}
