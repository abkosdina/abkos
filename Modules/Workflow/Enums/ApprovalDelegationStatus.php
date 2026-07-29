<?php

namespace Modules\Workflow\Enums;

enum ApprovalDelegationStatus: string
{
    case pending = 'pending';
    case active = 'active';
    case expired = 'expired';
    case cancelled = 'cancelled';
    case completed = 'completed';
}
