<?php

namespace Modules\Ledger\Enums;

enum LedgerTransactionType: string
{
    case DEPOSIT = 'deposit';
    case WITHDRAW = 'withdraw';
    case ESCROW_LOCK = 'escrow_lock';
    case ESCROW_RELEASE = 'escrow_release';
    case COMMISSION = 'commission';
    case REFUND = 'refund';
    case PENALTY = 'penalty';
    case TRANSFER = 'transfer';
    case ADJUSTMENT = 'adjustment';
    case SETTLEMENT = 'settlement';
    case FREEZE = 'freeze';
    case UNFREEZE = 'unfreeze';
}
