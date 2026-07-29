<?php

namespace Modules\Workflow\Repositories\Interfaces;

interface WorkflowStepRepositoryInterface
{
    public function find(int|string $id, array $columns = ['*']): ?object;

    public function getByDefinition(int|string $workflowDefinitionId): array;

    public function create(array $data): object;

    public function update(int|string $id, array $data): bool;
}
