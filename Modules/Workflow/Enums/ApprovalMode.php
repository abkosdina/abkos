<?php

namespace Modules\Workflow\Enums;

enum ApprovalMode: string
{
    case sequential = 'sequential';
    case parallel = 'parallel';
    case any = 'any';
    case all = 'all';
    case quorum = 'quorum';
}
