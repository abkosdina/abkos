<?php

namespace App\Repositories\Eloquent;

use App\Models\WorkflowInstanceStep;
use App\Repositories\Contracts\WorkflowStepRepository;
use DateTime;

class EloquentWorkflowStepRepository implements WorkflowStepRepository
{
    public function findById(int $id): ?WorkflowInstanceStep
    {
        return WorkflowInstanceStep::find($id);
    }

    public function findByInstance(int $instanceId): \Illuminate\Database\Eloquent\Collection
    {
        return WorkflowInstanceStep::where('workflow_instance_id', $instanceId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getLastStep(int $instanceId): ?WorkflowInstanceStep
    {
        return WorkflowInstanceStep::where('workflow_instance_id', $instanceId)
            ->orderBy('executed_at', 'desc')
            ->first();
    }

    public function create(array $data): WorkflowInstanceStep
    {
        return WorkflowInstanceStep::create($data);
    }

    public function findByIdempotencyKey(string $key): ?WorkflowInstanceStep
    {
        return WorkflowInstanceStep::where('idempotency_key', $key)->first();
    }

    public function findByExecutor(int $userId): \Illuminate\Database\Eloquent\Collection
    {
        return WorkflowInstanceStep::where('executed_by', $userId)
            ->orderBy('executed_at', 'desc')
            ->get();
    }

    public function findByDateRange(DateTime $from, DateTime $to): \Illuminate\Database\Eloquent\Collection
    {
        return WorkflowInstanceStep::whereBetween('executed_at', [$from, $to])
            ->orderBy('executed_at', 'desc')
            ->get();
    }
}
