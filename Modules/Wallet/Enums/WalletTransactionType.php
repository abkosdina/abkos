<?php

namespace Modules\Wallet\Enums;

enum WalletTransactionType: string
{
    case DEPOSIT = 'deposit';
    case WITHDRAW = 'withdraw';
    case ESCROW_LOCK = 'escrow_lock';
    case ESCROW_RELEASE = 'escrow_release';
    case REFUND = 'refund';
    case COMMISSION = 'commission';
    case PENALTY = 'penalty';
    case ADJUSTMENT = 'adjustment';
    case TRANSFER = 'transfer';
    case SETTLEMENT = 'settlement';
    case REWARD = 'reward';
    case BONUS = 'bonus';
}
