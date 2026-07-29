<?php

namespace App\Jobs;

use App\Models\WorkflowActionExecution;
use App\Services\Workflow\ActionExecutionService;
use Illuminate\Bus\Queueable;
use Illuminate\Bus\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Foundation\Bus\Dispatchable;

class ExecuteWorkflowActionJob implements ShouldQueue
{
    use Dispatchable, Queueable, SerializesModels;

    public function __construct(public int $executionId)
    {
    }

    public function handle(ActionExecutionService $executionService): void
    {
        $execution = WorkflowActionExecution::find($this->executionId);
        if (! $execution) {
            return;
        }

        if (! in_array($execution->status, ['pending', 'retrying'], true)) {
            return;
        }

        $executionService->executePendingExecution($execution);
    }
}
