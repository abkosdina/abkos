<?php

namespace Modules\Workflow\Actions;

use Modules\Workflow\Interfaces\ApprovalEngineInterface;
use Modules\Workflow\Models\ApprovalDefinition;
use Modules\Workflow\Models\ApprovalInstance;
use Modules\Workflow\Models\WorkflowInstance;

class StartApprovalAction
{
    public function __construct(protected ApprovalEngineInterface $engine)
    {
    }

    public function execute(WorkflowInstance $workflowInstance, ApprovalDefinition $approvalDefinition): ApprovalInstance
    {
        return $this->engine->start($workflowInstance, $approvalDefinition);
    }
}
