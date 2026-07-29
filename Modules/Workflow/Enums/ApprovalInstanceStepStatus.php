<?php

namespace Modules\Workflow\Enums;

enum ApprovalInstanceStepStatus: string
{
    case pending = 'pending';
    case active = 'active';
    case partially_approved = 'partially_approved';
    case approved = 'approved';
    case rejected = 'rejected';
    case returned_for_correction = 'returned_for_correction';
    case expired = 'expired';
    case cancelled = 'cancelled';
}
