<?php

namespace App\Events\Workflow;

use App\Models\WorkflowInstance;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * WorkflowStarted
 * 
 * Fired when a workflow is started for an entity
 */
class WorkflowStarted
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public WorkflowInstance $instance
    ) {}
}
