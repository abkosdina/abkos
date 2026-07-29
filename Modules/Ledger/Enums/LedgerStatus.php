<?php

namespace Modules\Ledger\Enums;

enum LedgerStatus: string
{
    case PENDING = 'pending';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';
    case FAILED = 'failed';
    case REVERSED = 'reversed';
}
