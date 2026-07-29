<?php

namespace Modules\Workflow\Repositories\Interfaces;

interface WorkflowConditionRepositoryInterface
{
    public function find(int|string $id, array $columns = ['*']): ?object;

    public function getActiveByWorkflowDefinition(int|string $workflowDefinitionId): array;

    public function create(array $data): object;
}
