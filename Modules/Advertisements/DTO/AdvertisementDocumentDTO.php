<?php

namespace Modules\Advertisements\DTO;

class AdvertisementDocumentDTO
{
    public function __construct(
        public int|string $documentId,
        public ?string $documentType = null,
    ) {
    }

    public function toArray(): array
    {
        return [
            'document_id' => $this->documentId,
            'document_type' => $this->documentType,
        ];
    }
}
