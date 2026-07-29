<?php

return [
    'name' => 'user-management',
    'default_guard' => 'web',
    'dashboard' => include __DIR__ . '/dashboard.php',
    'models' => [
        'user' => App\Models\User::class,
        'profile' => Modules\UserManagement\Models\UserProfile::class,
        'setting' => Modules\UserManagement\Models\UserSetting::class,
        'bank_account' => Modules\UserManagement\Models\UserBankAccount::class,
        'activity_log' => Modules\UserManagement\Models\ActivityLog::class,
    ],
];
