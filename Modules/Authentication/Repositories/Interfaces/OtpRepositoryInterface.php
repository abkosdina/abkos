<?php

namespace Modules\Authentication\Repositories\Interfaces;

interface OtpRepositoryInterface
{
    public function create(array $data): object;

    public function latestForMobile(string $mobile): ?object;

    public function incrementAttempts(int $id): void;

    public function markVerified(int $id, string $ipAddress, ?string $userAgent): void;
}
