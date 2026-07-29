<?php

namespace Modules\Documents\Services;

use Modules\Documents\Repositories\Interfaces\DocumentRepositoryInterface;
use Modules\Documents\Repositories\Interfaces\DocumentVersionRepositoryInterface;

class VersionService
{
    public function __construct(
        protected DocumentVersionRepositoryInterface $versionRepository,
        protected DocumentRepositoryInterface $documentRepository,
    ) {
    }

    public function createVersion(array $data): object
    {
        return $this->versionRepository->create($data);
    }

    public function getVersions(int|string $documentId): array
    {
        return $this->versionRepository->findByDocumentId($documentId);
    }

    public function createVersionFromCurrentState(object $document, string $notes = null): object
    {
        return $this->versionRepository->create([
            'uuid' => $document->uuid,
            'document_id' => $document->id,
            'version' => $document->current_version,
            'storage_provider' => $document->storage_provider,
            'mime_type' => $document->mime_type,
            'extension' => $document->extension,
            'original_name' => $document->original_name,
            'generated_name' => $document->generated_name,
            'hash' => $document->hash,
            'size' => $document->size,
            'status' => $document->status,
            'notes' => $notes,
        ]);
    }
}
