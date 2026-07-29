<?php

namespace Modules\Authentication\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Shared\Base\BaseController;
use Modules\Authentication\Services\OtpService;
use Modules\Authentication\Services\AuthenticationService;
use Modules\Authentication\Http\Requests\LoginRequest;
use Modules\Authentication\Http\Requests\RegisterRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Http\Request as HttpRequest;
use App\Models\User;
use App\Models\SiteSetting;
use Illuminate\Support\Str;

class AuthenticationController extends BaseController
{
    public function __construct(
        protected OtpService $otpService,
        protected AuthenticationService $authenticationService,
    ) {
    }

    public function requestOtp(Request $request): JsonResponse
    {
        $mobile = $request->input('mobile');
        $result = $this->otpService->requestOtp($mobile, $request->ip(), $request->userAgent());
        $message = $result['message'] ?? '';
        $persian = 'درخواست ارسال کد موفقیت‌آمیز بود.';
        if (stripos($message, 'sent') !== false || stripos($message, 'success') !== false) {
            $persian = 'کد تایید به شماره موبایل ارسال شد.';
        }

        return response()->json(['success' => ! empty($result['success']), 'message' => $persian]);
    }

    public function verifyOtp(Request $request): JsonResponse
    {
        $mobile = $request->input('mobile');
        $result = $this->otpService->verifyOtp($mobile, $request->input('otp'), $request->ip(), $request->userAgent());

        $msg = $result['message'] ?? '';
        if (! empty($result['success']) && $result['success'] === true) {
            $user = User::where('mobile', $mobile)->first();
            if ($user) {
                $loginResult = $this->authenticationService->login($user, $request->ip(), $request->userAgent());
                return response()->json(['success' => true, 'message' => 'کد تایید معتبر است. ورود موفق بود.', 'auth' => $loginResult]);
            }
            return response()->json(['success' => true, 'message' => 'کد تایید معتبر است اما کاربری با این شماره وجود ندارد. لطفاً ثبت‌نام کنید.']);
        }

        // map common OTP failure messages to Persian
        $persian = 'کد تایید معتبر نیست.';
        if (stripos($msg, 'expired') !== false) {
            $persian = 'کد تایید منقضی شده است.';
        } elseif (stripos($msg, 'Too many attempts') !== false || stripos($msg, 'attempt') !== false) {
            $persian = 'تعداد تلاش‌ها بیش از حد مجاز شده است.';
        } elseif (stripos($msg, 'not found') !== false) {
            $persian = 'کدی برای این شماره پیدا نشد.';
        }

        return response()->json(['success' => false, 'message' => $persian], 422);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()?->tokens()->delete();

        return response()->json(['success' => true, 'message' => 'Logged out successfully.']);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'success' => true,
            'user' => $user,
            'is_super_admin' => $user ? $user->hasRole('Super Admin') : false,
            'role_names' => $user ? $user->getRoleNames()->values()->all() : [],
        ]);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $mobile = $request->input('mobile');

        if ($request->filled('otp')) {
            $verify = $this->otpService->verifyOtp($mobile, $request->input('otp'), $request->ip(), $request->userAgent());
            if (! empty($verify['success'])) {
                $user = User::where('mobile', $mobile)->first();
                if ($user) {
                    $loginResult = $this->authenticationService->login($user, $request->ip(), $request->userAgent());
                    return response()->json(['success' => true, 'message' => 'ورود موفق بود.', 'auth' => $loginResult]);
                }
                return response()->json(['success' => false, 'message' => 'کاربری با این شماره وجود ندارد. لطفاً ابتدا ثبت‌نام کنید.'], 404);
            }

            $msg = $verify['message'] ?? '';
            $persian = 'کد تایید اشتباه است.';
            if (stripos($msg, 'expired') !== false) {
                $persian = 'کد تایید منقضی شده است.';
            }
            return response()->json(['success' => false, 'message' => $persian], 422);
        }

        if ($request->filled('password')) {
            $user = User::where('mobile', $mobile)->first();
            if (! $user) {
                return response()->json(['success' => false, 'message' => 'کاربری با این شماره وجود ندارد. لطفاً ثبت‌نام کنید.'], 404);
            }

            if (! Hash::check($request->input('password'), $user->password)) {
                return response()->json(['success' => false, 'message' => 'شماره یا رمز عبور اشتباه است.'], 401);
            }

            $loginResult = $this->authenticationService->login($user, $request->ip(), $request->userAgent());
            return response()->json(['success' => true, 'message' => 'ورود موفق بود.', 'auth' => $loginResult]);
        }

        $sent = $this->otpService->requestOtp($mobile, $request->ip(), $request->userAgent());
        if (! empty($sent['success'])) {
            return response()->json(['success' => true, 'message' => 'کد تایید به شماره موبایل ارسال شد.']);
        }

        return response()->json(['success' => false, 'message' => 'خطا در ارسال کد تایید. لطفاً بعداً تلاش کنید.'], 500);
    }

    public function register(RegisterRequest $request): JsonResponse
    {
        $isEnabled = SiteSetting::getValue('broker_registration_enabled', true);
        if (! $isEnabled) {
            return response()->json([
                'success' => false,
                'message' => 'عضویت موقتا غیر فعال است. برای راهنمایی با راهبر سیستم تماس بگیرید.',
            ], 403);
        }

        $password = $request->input('password');
        if (! $password) {
            $password = Str::random(40);
        }

        $email = $request->filled('email') ? $request->input('email') : null;

        $user = User::create([
            'name' => $request->input('full_name'),
            'mobile' => $request->input('mobile'),
            'email' => $email,
            'password' => Hash::make($password),
        ]);

        return response()->json(['success' => true, 'message' => 'ثبت‌نام با موفقیت انجام شد.', 'user' => $user]);
    }

    public function toggleBrokerRegistration(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user || ! $user->hasRole('Super Admin')) {
            return response()->json(['success' => false, 'message' => 'دسترسی محدود است.'], 403);
        }

        $enabled = filter_var($request->input('enabled', true), FILTER_VALIDATE_BOOLEAN);
        SiteSetting::setValue('broker_registration_enabled', $enabled, 'Enable or disable broker registration', 'boolean');

        return response()->json([
            'success' => true,
            'message' => $enabled ? 'عضویت اربران فعال شد.' : 'عضویت اربران غیرفعال شد.',
            'enabled' => $enabled,
        ]);
    }

    public function brokerRegistrationStatus(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user || ! $user->hasRole('Super Admin')) {
            return response()->json(['success' => false, 'message' => 'دسترسی محدود است.'], 403);
        }

        return response()->json([
            'success' => true,
            'enabled' => SiteSetting::getValue('broker_registration_enabled', true),
        ]);
    }
}
