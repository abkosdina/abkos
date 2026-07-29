<?php

namespace Modules\Authentication\Services;

use Illuminate\Support\Str;
use Modules\Authentication\Enums\OtpStatus;
use Modules\Authentication\Repositories\Interfaces\OtpRepositoryInterface;
use Modules\Authentication\Services\SmsService;
use Modules\Authentication\Services\SecurityService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class OtpService
{
    public function __construct(
        protected OtpRepositoryInterface $repository,
        protected SmsService $smsService,
        protected SecurityService $securityService,
    ) {
    }

    public function requestOtp(string $mobile, string $ipAddress, ?string $userAgent = null): array
    {
        $this->securityService->checkRateLimit($ipAddress, $mobile);

        $otp = (string) random_int(10000, 99999);
        $otpHash = Hash::make($otp);

        $this->repository->create([
            'mobile' => $mobile,
            'otp_hash' => $otpHash,
            'attempt_count' => 0,
            'max_attempts' => 5,
            'expires_at' => now()->addMinutes(5),
            'ip_address' => $ipAddress,
            'device_information' => $userAgent,
            'status' => OtpStatus::PENDING->value,
        ]);

        // Log the plain OTP for debugging/traceability as requested
        Log::info('OTP generated', ['mobile' => $mobile, 'otp' => $otp]);

        $this->smsService->send($mobile, 'Your OTP is '.$otp);

        return ['success' => true, 'message' => 'OTP sent successfully.'];
    }

    public function verifyOtp(string $mobile, string $otp, string $ipAddress, ?string $userAgent = null): array
    {
        $record = $this->repository->latestForMobile($mobile);

        if (! $record) {
            return ['success' => false, 'message' => 'OTP not found.'];
        }

        if ($record->status !== OtpStatus::PENDING->value && $record->status !== OtpStatus::SENT->value) {
            return ['success' => false, 'message' => 'OTP is no longer valid.'];
        }

        if ($record->expires_at && now()->gt($record->expires_at)) {
            return ['success' => false, 'message' => 'OTP expired.'];
        }

        if ($record->attempt_count >= $record->max_attempts) {
            return ['success' => false, 'message' => 'Too many attempts.'];
        }

        if (! Hash::check($otp, $record->otp_hash)) {
            $this->repository->incrementAttempts($record->id);
            return ['success' => false, 'message' => 'Invalid OTP.'];
        }

        $this->repository->markVerified($record->id, $ipAddress, $userAgent);

        return ['success' => true, 'message' => 'OTP verified successfully.'];
    }
}
