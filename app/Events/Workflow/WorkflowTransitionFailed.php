<?php

namespace App\Events\Workflow;

use App\Models\WorkflowInstance;
use Exception;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * WorkflowTransitionFailed
 * 
 * Fired when a workflow transition fails
 */
class WorkflowTransitionFailed
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public WorkflowInstance $instance,
        public Exception $exception
    ) {}
}
