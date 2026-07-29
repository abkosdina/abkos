<?php

namespace App\Events\Workflow;

use App\Models\WorkflowInstance;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * WorkflowCancelled
 * 
 * Fired when a workflow is cancelled by user or system
 */
class WorkflowCancelled
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public WorkflowInstance $instance
    ) {}
}
