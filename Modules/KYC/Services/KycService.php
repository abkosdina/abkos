<?php

namespace Modules\KYC\Services;

use Illuminate\Support\Str;
use Modules\KYC\Enums\KycStatus;
use Modules\KYC\Models\KycProfile;
use Modules\KYC\Models\KycRejectionReason;
use Modules\KYC\Models\KycReviewLog;
use Modules\KYC\Models\KycStatusHistory;
use Modules\KYC\Repositories\Interfaces\KycProfileRepositoryInterface;
use Modules\KYC\Repositories\Interfaces\KycRequestRepositoryInterface;

class KycService
{
    public function __construct(
        protected KycRequestRepositoryInterface $requestRepository,
        protected KycProfileRepositoryInterface $profileRepository,
    ) {
    }

    public function list(array $filters = []): array
    {
        if (!empty($filters['user_id'])) {
            return $this->requestRepository->getByUserId($filters['user_id']);
        }

        return $this->requestRepository->all();
    }

    public function find(int|string $id): ?object
    {
        return $this->requestRepository->find($id);
    }

    public function create(array $data): object
    {
        $payload = array_merge($data, [
            'uuid' => Str::uuid()->toString(),
            'status' => $data['status'] ?? KycStatus::PENDING->value,
            'priority' => $data['priority'] ?? 1,
        ]);

        $request = $this->requestRepository->create($payload);

        $this->recordStatusHistory($request, null, $request->status, null, 'system');

        return $request;
    }

    public function update(int|string $id, array $data): bool
    {
        return $this->requestRepository->update($id, $data);
    }

    public function submit(int|string $id, ?object $user = null): ?object
    {
        $request = $this->requestRepository->find($id);

        if (!$request) {
            throw new \InvalidArgumentException('KYC request not found.');
        }

        $oldStatus = $request->status;

        $this->requestRepository->update($id, [
            'status' => KycStatus::UNDER_REVIEW->value,
            'started_at' => now(),
        ]);

        $request = $this->requestRepository->find($id);

        $this->recordStatusHistory($request, $oldStatus, $request->status, $user, 'review');
        $this->recordReviewLog($request, 'submit', $oldStatus, $request->status, $user);

        return $request;
    }

    public function approve(int|string $id, ?object $user = null, ?string $comment = null): object
    {
        $request = $this->requestRepository->find($id);

        if (!$request) {
            throw new \InvalidArgumentException('KYC request not found.');
        }

        $oldStatus = $request->status;

        $this->requestRepository->update($id, [
            'status' => KycStatus::APPROVED->value,
            'completed_at' => now(),
        ]);

        $request = $this->requestRepository->find($id);

        $this->recordStatusHistory($request, $oldStatus, $request->status, $user, 'approver', $comment);
        $this->recordReviewLog($request, 'approve', $oldStatus, $request->status, $user, $comment);

        return $request;
    }

    public function reject(int|string $id, string $reason, ?string $comment = null, array $requiredCorrections = [], ?object $user = null): object
    {
        $request = $this->requestRepository->find($id);

        if (!$request) {
            throw new \InvalidArgumentException('KYC request not found.');
        }

        $oldStatus = $request->status;

        $this->requestRepository->update($id, [
            'status' => KycStatus::REJECTED->value,
        ]);

        $request = $this->requestRepository->find($id);

        KycRejectionReason::create([
            'uuid' => Str::uuid()->toString(),
            'kyc_request_id' => $request->id,
            'operator_id' => $user?->id,
            'reason' => $reason,
            'comment' => $comment,
            'required_corrections' => $requiredCorrections,
        ]);

        $this->recordStatusHistory($request, $oldStatus, $request->status, $user, 'approver', $comment);
        $this->recordReviewLog($request, 'reject', $oldStatus, $request->status, $user, $comment);

        return $request;
    }

    public function updateProfile(int|string $requestId, array $data): object
    {
        $request = $this->requestRepository->find($requestId);

        if (!$request) {
            throw new \InvalidArgumentException('KYC request not found.');
        }

        $profile = $this->profileRepository->findByUserId($request->user_id);
        $payload = array_merge($data, [
            'uuid' => $profile?->uuid ?? Str::uuid()->toString(),
            'user_id' => $request->user_id,
        ]);

        if ($profile) {
            $this->profileRepository->update($profile->id, $payload);
            return $this->profileRepository->find($profile->id);
        }

        return $this->profileRepository->create($payload);
    }

    protected function recordReviewLog(object $request, string $action, ?string $oldStatus, ?string $newStatus, ?object $user = null, ?string $comment = null): void
    {
        KycReviewLog::create([
            'uuid' => Str::uuid()->toString(),
            'kyc_request_id' => $request->id,
            'reviewer_id' => $user?->id,
            'action' => $action,
            'role' => $user?->role?->name ?? null,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'comment' => $comment,
            'ip_address' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
        ]);
    }

    protected function recordStatusHistory(object $request, ?string $oldStatus, ?string $newStatus, ?object $user = null, ?string $role = null, ?string $comment = null): void
    {
        KycStatusHistory::create([
            'uuid' => Str::uuid()->toString(),
            'kyc_request_id' => $request->id,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'changed_by' => $user?->id,
            'role' => $role,
            'comment' => $comment,
            'ip_address' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
        ]);
    }
}
