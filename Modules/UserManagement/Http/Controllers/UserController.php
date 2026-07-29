<?php

namespace Modules\UserManagement\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Modules\Advertisements\Models\AdvertisementLog;
use Modules\Shared\Base\BaseController;
use Modules\UserManagement\Services\UserService;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Log;

class UserController extends BaseController
{
    public function __construct(protected UserService $service)
    {
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $roleFilter = $request->query('role');
            $search = trim((string) $request->query('search', ''));
            $perPage = (int) $request->query('per_page', 15);
            $perPage = $perPage > 0 ? min($perPage, 100) : 15;

            $query = User::query()
                ->with('roles')
                ->when($roleFilter, function ($query, $roleFilter) {
                    $query->whereHas('roles', function ($query) use ($roleFilter) {
                        $query->where('name', $roleFilter);
                    });
                })
                ->when($search !== '', function ($query) use ($search) {
                    $query->where(function ($query) use ($search) {
                        $query->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('mobile', 'like', "%{$search}%");
                    });
                })
                ->orderByDesc('created_at');

            $users = $query->paginate($perPage);
        } catch (\Throwable $e) {
            Log::error('UserController@index error: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['success' => false, 'message' => 'خطای سرور هنگام بارگذاری کاربران.'], 500);
        }

        $payload = $users->map(function (User $user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'mobile' => $user->mobile,
                'status' => $user->status ?? 'active',
                'is_verified' => (bool) $user->is_verified,
                'is_suspended' => (bool) $user->is_suspended,
                'moderation_note' => $user->moderation_note,
                'created_at' => $user->created_at?->toISOString(),
                'roles' => $user->roles->pluck('name')->values()->all(),
                'role_labels' => $user->roles->map(fn ($role) => $role->slug_fa ?? $role->display_name ?? $role->name)->values()->all(),
                'permissions' => $user->getPermissionNames()->values()->all(),
            ];
        })->values();

        return response()->json([
            'success' => true,
            'data' => $payload,
            'meta' => [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'total' => $users->total(),
                'per_page' => $users->perPage(),
            ],
            'message' => 'کاربران با موفقیت بارگذاری شدند.',
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $messages = [
            'name.required' => 'نام کاربر الزامی است.',
            'name.string' => 'نام کاربر باید متنی باشد.',
            'email.email' => 'ایمیل وارد شده معتبر نیست.',
            'email.unique' => 'این ایمیل قبلاً ثبت شده است.',
            'mobile.string' => 'شماره همراه باید متنی باشد.',
            'mobile.unique' => 'این شماره همراه قبلاً ثبت شده است.',
            'password.string' => 'رمز عبور باید متنی باشد.',
            'password.min' => 'رمز عبور باید حداقل 6 کاراکتر باشد.',
            'password.confirmed' => 'تأیید رمز عبور با رمز عبور مطابقت ندارد.',
            'roles.array' => 'نقش‌ها باید آرایه باشند.',
            'roles.*.string' => 'هر نقش باید یک رشته باشد.',
            'permissions.array' => 'سطح دسترسی‌ها باید آرایه باشند.',
            'permissions.*.string' => 'هر سطح دسترسی باید یک رشته باشد.',
            'is_verified.boolean' => 'فیلد تأیید شده باید مقدار بولی باشد.',
            'is_suspended.boolean' => 'فیلد معلق شده باید مقدار بولی باشد.',
        ];

        $data = $request->validate([
            'name' => 'required|string',
            'email' => 'nullable|email|unique:users,email',
            'mobile' => 'nullable|string|unique:users,mobile',
            'password' => 'nullable|string|min:6|confirmed',
            'password_confirmation' => 'nullable|string',
            'status' => 'nullable|string',
            'is_verified' => 'nullable|boolean',
            'is_suspended' => 'nullable|boolean',
            'moderation_note' => 'nullable|string',
            'roles' => 'nullable|array',
            'roles.*' => 'string',
            'permissions' => 'nullable|array',
            'permissions.*' => 'string',
        ], $messages);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'] ?? null,
            'mobile' => $data['mobile'] ?? null,
            'password' => Hash::make($data['password'] ?? '12345678'),
            'status' => $data['status'] ?? 'active',
            'is_verified' => $data['is_verified'] ?? false,
            'is_suspended' => $data['is_suspended'] ?? false,
            'moderation_note' => $data['moderation_note'] ?? null,
        ]);

        if (! empty($data['roles'])) {
            $user->syncRoles($data['roles']);
        }

        if (! empty($data['permissions'])) {
            $user->syncPermissions($data['permissions']);
        }

        return response()->json([
            'success' => true,
            'data' => $user->load('roles'),
            'message' => 'کاربر با موفقیت ایجاد شد.',
        ]);
    }

    public function show($user): JsonResponse
    {
        $record = User::with('roles')->findOrFail($user);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $record->id,
                'name' => $record->name,
                'email' => $record->email,
                'mobile' => $record->mobile,
                'status' => $record->status ?? 'active',
                'is_verified' => (bool) $record->is_verified,
                'is_suspended' => (bool) $record->is_suspended,
                'moderation_note' => $record->moderation_note,
                'roles' => $record->roles->pluck('name')->values()->all(),
                'role_labels' => $record->roles->map(fn ($role) => $role->slug_fa ?? $role->display_name ?? $role->name)->values()->all(),
                'permissions' => $record->getPermissionNames()->values()->all(),
            ],
            'message' => 'کاربر با موفقیت دریافت شد.',
        ]);
    }

    public function update(Request $request, $user): JsonResponse
    {
        $record = User::findOrFail($user);
        $data = $request->validate([
            'name' => 'nullable|string',
            'email' => 'nullable|email|unique:users,email,' . $record->id,
            'mobile' => 'nullable|string|unique:users,mobile,' . $record->id,
            'password' => 'nullable|string|min:6|confirmed',
            'password_confirmation' => 'nullable|string',
            'roles' => 'nullable|array',
            'roles.*' => 'string',
            'permissions' => 'nullable|array',
            'permissions.*' => 'string',
            'status' => 'nullable|string',
            'is_verified' => 'nullable|boolean',
            'is_suspended' => 'nullable|boolean',
            'moderation_note' => 'nullable|string',
        ]);

        if (! empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $record->fill($data);
        $record->save();

        if (array_key_exists('roles', $data)) {
            $record->syncRoles($data['roles']);
        }

        if (array_key_exists('permissions', $data)) {
            $record->syncPermissions($data['permissions']);
        }

        return response()->json([
            'success' => true,
            'data' => $record->fresh()->load('roles'),
            'message' => 'کاربر با موفقیت به‌روزرسانی شد.',
        ]);
    }

    public function destroy($user): JsonResponse
    {
        $record = User::findOrFail($user);

        DB::transaction(function () use ($record) {
            AdvertisementLog::where('user_id', $record->id)->update(['user_id' => null]);
            $record->roles()->detach();
            $record->permissions()->detach();
            $record->delete();
        });

        return response()->json([
            'success' => true,
            'data' => null,
            'message' => 'کاربر با موفقیت حذف شد.',
        ]);
    }

    public function roles(): JsonResponse
    {
        try {
            return response()->json([
                'success' => true,
                'data' => Role::query()->orderBy('name')->get(['id', 'name', 'display_name', 'slug_fa']),
            ]);
        } catch (\Throwable $e) {
            Log::error('UserController@roles error: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['success' => false, 'message' => 'خطای سرور هنگام بارگذاری نقش‌ها.'], 500);
        }
    }

    public function permissions(): JsonResponse
    {
        try {
            return response()->json([
                'success' => true,
                'data' => Permission::query()->orderBy('name')->get(['id', 'name', 'display_name', 'slug_fa']),
            ]);
        } catch (\Throwable $e) {
            Log::error('UserController@permissions error: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['success' => false, 'message' => 'خطای سرور هنگام بارگذاری دسترسی‌ها.'], 500);
        }
    }

    public function moderate(Request $request, $user): JsonResponse
    {
        $record = User::findOrFail($user);
        $action = $request->input('action');

        return match ($action) {
            'approve' => $this->applyModeration($record, ['status' => 'active', 'is_verified' => true], 'کاربر تأیید شد.'),
            'reject' => $this->applyModeration($record, ['status' => 'rejected', 'is_verified' => false], 'درخواست کاربر رد شد.'),
            'verify' => $this->applyModeration($record, ['is_verified' => true], 'کاربر تأیید هویت شد.'),
            'unverify' => $this->applyModeration($record, ['is_verified' => false], 'تأیید هویت حذف شد.'),
            'suspend' => $this->applyModeration($record, ['is_suspended' => true, 'status' => 'suspended'], 'کاربر معلق شد.'),
            'unsuspend' => $this->applyModeration($record, ['is_suspended' => false, 'status' => 'active'], 'تعلیق کاربر برداشته شد.'),
            default => response()->json(['success' => false, 'message' => 'اقدام نامعتبر است.'], 422),
        };
    }

    private function applyModeration(User $user, array $payload, string $message): JsonResponse
    {
        $user->fill($payload);
        $user->moderation_note = request('moderation_note', $user->moderation_note);
        $user->save();

        return response()->json(['success' => true, 'message' => $message, 'data' => $user->fresh()]);
    }
}
