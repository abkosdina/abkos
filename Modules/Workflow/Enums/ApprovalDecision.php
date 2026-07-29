<?php

namespace Modules\Workflow\Enums;

enum ApprovalDecision: string
{
    case approved = 'approved';
    case rejected = 'rejected';
    case returned_for_correction = 'returned_for_correction';
    case delegated = 'delegated';
}
