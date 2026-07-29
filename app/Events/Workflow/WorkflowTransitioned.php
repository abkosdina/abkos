<?php

namespace App\Events\Workflow;

use App\Models\WorkflowInstance;
use App\Models\WorkflowInstanceStep;
use App\Models\WorkflowState;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * WorkflowTransitioned
 * 
 * Fired when a workflow transitions to a new state
 */
class WorkflowTransitioned
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public WorkflowInstance $instance,
        public WorkflowState $previousState,
        public WorkflowState $newState,
        public WorkflowInstanceStep $step
    ) {}
}
