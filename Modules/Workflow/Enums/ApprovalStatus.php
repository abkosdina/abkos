<?php

namespace Modules\Workflow\Enums;

enum ApprovalStatus: string
{
    case pending = 'pending';
    case in_progress = 'in_progress';
    case partially_approved = 'partially_approved';
    case approved = 'approved';
    case rejected = 'rejected';
    case returned_for_correction = 'returned_for_correction';
    case expired = 'expired';
    case cancelled = 'cancelled';
}
