<?php

namespace Modules\Workflow\Repositories\Interfaces;

interface WorkflowTransitionRepositoryInterface
{
    public function getByFromStep(int|string $fromStepId): array;

    public function getByWorkflowDefinition(int|string $workflowDefinitionId): array;

    public function find(int|string $id, array $columns = ['*']): ?object;
}
