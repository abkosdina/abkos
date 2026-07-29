<?php

namespace Modules\Deals\Enums;

enum DealStatus: string
{
    case Pending = 'pending';
    case AwaitingPayment = 'awaiting_payment';
    case PaymentProcessing = 'payment_processing';
    case PaymentCompleted = 'payment_completed';
    case EscrowActive = 'escrow_active';
    case SellerProcessing = 'seller_processing';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
    case Expired = 'expired';
    case Disputed = 'disputed';
    case Refunded = 'refunded';
    case Closed = 'closed';
    case Failed = 'failed';

    public static function fromValue(?string $value): self
    {
        return self::tryFrom(strtolower((string) ($value ?? self::Pending->value))) ?? self::Pending;
    }
}
