<?php

namespace Modules\KYC\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class KycDocumentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'document_type' => $this->document_type,
            'mime_type' => $this->mime_type,
            'file_size' => $this->file_size,
            'document_status' => $this->document_status,
            'uploaded_by' => $this->uploaded_by,
            'uploaded_at' => $this->uploaded_at?->toIso8601String(),
            'reviewed_by' => $this->reviewed_by,
            'reviewed_at' => $this->reviewed_at?->toIso8601String(),
            'rejection_reason' => $this->rejection_reason,
            'metadata' => $this->when(optional($request->user())->hasPermissionTo('kyc.review') || optional($request->user())->id === $this->uploaded_by, fn () => $this->metadata),
            'download_url' => $this->when(optional($request->user())->hasPermissionTo('kyc.download_documents') || optional($request->user())->id === $this->uploaded_by, fn () => route('kyc.documents.download', ['kycRequest' => $this->kyc_request_id, 'document' => $this->id])),
        ];
    }
}
