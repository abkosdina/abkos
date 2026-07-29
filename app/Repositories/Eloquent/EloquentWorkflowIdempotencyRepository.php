<?php

namespace App\Repositories\Eloquent;

use App\Models\WorkflowIdempotencyKey;
use App\Repositories\Contracts\WorkflowIdempotencyRepository;

class EloquentWorkflowIdempotencyRepository implements WorkflowIdempotencyRepository
{
    public function findByKey(string $key): ?WorkflowIdempotencyKey
    {
        return WorkflowIdempotencyKey::where('key', $key)->first();
    }

    public function exists(string $key): bool
    {
        return WorkflowIdempotencyKey::where('key', $key)->exists();
    }

    public function create(array $data): WorkflowIdempotencyKey
    {
        return WorkflowIdempotencyKey::create($data);
    }

    public function findByInstance(int $instanceId): \Illuminate\Database\Eloquent\Collection
    {
        return WorkflowIdempotencyKey::where('workflow_instance_id', $instanceId)
            ->orderBy('executed_at', 'desc')
            ->get();
    }

    public function cleanupOld(int $days = 30): int
    {
        $cutoffDate = now()->subDays($days);

        return WorkflowIdempotencyKey::where('executed_at', '<', $cutoffDate)->delete();
    }
}
