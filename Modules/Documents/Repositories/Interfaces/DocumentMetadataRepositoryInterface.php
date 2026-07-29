<?php

namespace Modules\Documents\Repositories\Interfaces;

interface DocumentMetadataRepositoryInterface
{
    public function create(array $data): object;

    public function getByDocument(int|string $documentId): array;
}
