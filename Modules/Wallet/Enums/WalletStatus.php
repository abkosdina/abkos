<?php

namespace Modules\Wallet\Enums;

enum WalletStatus: string
{
    case ACTIVE = 'active';
    case BLOCKED = 'blocked';
    case SUSPENDED = 'suspended';
    case FROZEN = 'frozen';
    case CLOSED = 'closed';
}
