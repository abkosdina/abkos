<?php

namespace Modules\Documents\Services;

use Modules\Documents\Repositories\Interfaces\DocumentLinkRepositoryInterface;
use Modules\Documents\Repositories\Interfaces\DocumentRepositoryInterface;

class SharingService
{
    public function __construct(
        protected DocumentRepositoryInterface $documentRepository,
        protected DocumentLinkRepositoryInterface $linkRepository,
    ) {
    }

    public function createShareLink(int|string $documentId, ?\DateTimeInterface $expiresAt = null, bool $isPublic = false): object
    {
        $document = $this->documentRepository->find($documentId);

        if (!$document) {
            throw new \InvalidArgumentException('Document not found.');
        }

        return $this->linkRepository->create([
            'uuid' => (string) now()->timestamp . '-' . $documentId,
            'document_id' => $documentId,
            'link' => bin2hex(random_bytes(16)),
            'expires_at' => $expiresAt?->format('Y-m-d H:i:s'),
            'is_public' => $isPublic,
        ]);
    }
}
