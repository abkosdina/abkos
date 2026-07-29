<?php

namespace Modules\Workflow\Repositories\Interfaces;

interface WorkflowActionRepositoryInterface
{
    public function create(array $data): object;

    public function find(int|string $id, array $columns = ['*']): ?object;

    public function getByWorkflowDefinition(int|string $workflowDefinitionId): array;

    public function getByStep(int|string $stepId): array;
}
