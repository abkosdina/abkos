<?php

namespace Modules\Authentication\Repositories\Eloquent;

use Modules\Authentication\Enums\OtpStatus;
use Modules\Authentication\Models\OtpRequest;
use Modules\Authentication\Repositories\Interfaces\OtpRepositoryInterface;

class OtpRepository implements OtpRepositoryInterface
{
    public function __construct(protected OtpRequest $model)
    {
    }

    public function create(array $data): object
    {
        return $this->model->query()->create($data);
    }

    public function latestForMobile(string $mobile): ?object
    {
        return $this->model->query()->where('mobile', $mobile)->latest('created_at')->first();
    }

    public function incrementAttempts(int $id): void
    {
        $this->model->query()->findOrFail($id)->increment('attempt_count');
    }

    public function markVerified(int $id, string $ipAddress, ?string $userAgent): void
    {
        $this->model->query()->findOrFail($id)->update([
            'verified_at' => now(),
            'status' => OtpStatus::VERIFIED->value,
            'ip_address' => $ipAddress,
            'device_information' => $userAgent,
        ]);
    }
}
