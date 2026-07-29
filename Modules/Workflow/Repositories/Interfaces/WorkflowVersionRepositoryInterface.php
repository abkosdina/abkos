<?php

namespace Modules\Workflow\Repositories\Interfaces;

interface WorkflowVersionRepositoryInterface
{
    public function find(int|string $id, array $columns = ['*']): ?object;

    public function create(array $data): object;

    public function activateVersion(int|string $id): bool;

    public function getVersionsForDefinition(int|string $workflowDefinitionId): array;
}
