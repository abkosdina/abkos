<?php

namespace Modules\Documents\Services;

use Modules\Documents\Repositories\Interfaces\DocumentDownloadLogRepositoryInterface;
use Modules\Documents\Repositories\Interfaces\DocumentRepositoryInterface;

class DownloadService
{
    public function __construct(
        protected DocumentRepositoryInterface $documentRepository,
        protected DocumentDownloadLogRepositoryInterface $downloadLogRepository,
    ) {
    }

    public function recordDownload(int|string $documentId, ?int $userId = null, ?string $ipAddress = null, ?string $userAgent = null, ?string $device = null): object
    {
        return $this->downloadLogRepository->create([
            'uuid' => (string) now()->timestamp . '-' . $documentId,
            'document_id' => $documentId,
            'user_id' => $userId,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'device' => $device,
        ]);
    }
}
