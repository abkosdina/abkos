<?php

namespace Modules\Workflow\Repositories\Interfaces;

interface WorkflowAssignmentRepositoryInterface
{
    public function create(array $data): object;

    public function find(int|string $id, array $columns = ['*']): ?object;

    public function getByStep(int|string $stepId): array;
}
