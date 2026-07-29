<?php

namespace Modules\KYC\Enums;

enum KycStatus: string
{
    case PENDING = 'pending';
    case UNDER_REVIEW = 'under_review';
    case NEED_CORRECTION = 'need_correction';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
    case EXPIRED = 'expired';
    case CANCELLED = 'cancelled';
}
