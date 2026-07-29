<?php

namespace Modules\Authentication\Enums;

enum OtpStatus: string
{
    case PENDING = 'pending';
    case SENT = 'sent';
    case VERIFIED = 'verified';
    case EXPIRED = 'expired';
    case BLOCKED = 'blocked';
    case FAILED = 'failed';
}
