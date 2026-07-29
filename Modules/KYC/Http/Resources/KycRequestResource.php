<?php

namespace Modules\KYC\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class KycRequestResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'status' => $this->status,
            'user_id' => $this->user_id,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'submitted_at' => $this->submitted_at?->toIso8601String(),
            'approved_at' => $this->approved_at?->toIso8601String(),
            'rejected_at' => $this->rejected_at?->toIso8601String(),
            'documents' => KycDocumentResource::collection($this->whenLoaded('documents')),
            'identity_snapshot' => $this->whenLoaded('identitySnapshots', function () {
                return KycIdentitySnapshotResource::make($this->identitySnapshots?->latest()->first());
            }),
            'rejection_reasons' => $this->whenLoaded('rejections', function () {
                return $this->rejections->pluck('reason')->toArray();
            }),
            'review_logs' => $this->whenLoaded('reviewLogs', function () {
                return $this->reviewLogs->map(function ($log) {
                    return [
                        'reviewed_by' => $log->reviewed_by,
                        'action' => $log->action,
                        'comment' => $log->comment,
                        'created_at' => $log->created_at?->toIso8601String(),
                    ];
                })->toArray();
            }),
        ];
    }
}
