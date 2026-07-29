<?php

namespace Modules\Documents\Repositories\Interfaces;

interface DocumentVersionRepositoryInterface
{
    public function create(array $data): object;

    public function getByDocument(int|string $documentId): array;

    public function findLatestVersion(int|string $documentId): ?object;
}
