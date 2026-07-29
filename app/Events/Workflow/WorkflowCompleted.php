<?php

namespace App\Events\Workflow;

use App\Models\WorkflowInstance;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * WorkflowCompleted
 * 
 * Fired when a workflow reaches a final state and is completed
 */
class WorkflowCompleted
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public WorkflowInstance $instance
    ) {}
}
