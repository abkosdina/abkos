<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\SiteSetting;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

$config = [
    'super-admin' => [
        ['id' => 'dashboard', 'items' => ['نمای کلی', 'آمار و گزارش', 'فعالیت‌های اخیر']],
        ['id' => 'users', 'items' => ['مدیران', 'اپراتورها', 'کاربران']],
        ['id' => 'ads', 'items' => ['همه آگهی‌ها', 'تأیید آگهی‌ها', 'آگهی‌های معلق']],
        ['id' => 'orders', 'items' => ['همه سفارش‌ها', 'در حال انجام', 'تکمیل‌شده']],
        ['id' => 'wallet', 'items' => ['تراکنش‌ها', 'درخواست‌های برداشت', 'اعتبار']],
        ['id' => 'messages', 'items' => ['گفتگوها', 'آرشیو پیام‌ها']],
        ['id' => 'support', 'items' => ['تیکت‌ها', 'سوالات متداول']],
    ],
    'admin' => [
        ['id' => 'dashboard', 'items' => ['نمای کلی', 'آمار و گزارش', 'فعالیت‌های اخیر']],
        ['id' => 'ads', 'items' => ['همه آگهی‌ها', 'تأیید آگهی‌ها', 'آگهی‌های معلق']],
        ['id' => 'orders', 'items' => ['همه سفارش‌ها', 'در حال انجام', 'تکمیل‌شده']],
        ['id' => 'wallet', 'items' => ['تراکنش‌ها', 'درخواست‌های برداشت', 'اعتبار']],
        ['id' => 'messages', 'items' => ['گفتگوها', 'آرشیو پیام‌ها']],
        ['id' => 'support', 'items' => ['تیکت‌ها', 'سوالات متداول']],
    ],
    'operator' => [
        ['id' => 'dashboard', 'items' => ['نمای کلی', 'فعالیت‌های اخیر']],
        ['id' => 'ads', 'items' => ['همه آگهی‌ها', 'تأیید آگهی‌ها']],
        ['id' => 'messages', 'items' => ['گفتگوها', 'آرشیو پیام‌ها']],
        ['id' => 'support', 'items' => ['تیکت‌ها']],
    ],
    'finance' => [
        ['id' => 'dashboard', 'items' => ['نمای کلی', 'آمار و گزارش']],
        ['id' => 'orders', 'items' => ['همه سفارش‌ها', 'در حال انجام']],
        ['id' => 'wallet', 'items' => ['تراکنش‌ها', 'درخواست‌های برداشت']],
        ['id' => 'contracts', 'items' => ['قراردادهای فعال', 'قراردادهای تکمیل‌شده']],
        ['id' => 'support', 'items' => ['تیکت‌ها']],
    ],
    'user' => [
        ['id' => 'dashboard', 'items' => ['نمای کلی', 'فعالیت‌های اخیر', 'آمار حساب']],
        ['id' => 'account', 'items' => ['پروفایل', 'اطلاعات شخصی', 'حساب‌های بانکی', 'تنظیمات حساب', 'امنیت حساب']],
        ['id' => 'kyc', 'items' => ['وضعیت احراز هویت', 'ارسال مدارک']],
        ['id' => 'ads', 'items' => ['همه آگهی‌های من', 'ثبت آگهی جدید']],
        ['id' => 'search', 'items' => ['همه آگهی‌ها', 'علاقه‌مندی‌ها']],
        ['id' => 'negotiations', 'items' => ['مذاکرات دریافتی', 'مذاکرات ارسالی']],
        ['id' => 'orders', 'items' => ['سفارش‌های خرید', 'سفارش‌های فروش']],
        ['id' => 'wallet', 'items' => ['موجودی', 'تراکنش‌ها']],
        ['id' => 'support', 'items' => ['ثبت تیکت', 'تیکت‌های من']],
    ],
];

DB::transaction(function() use ($config) {
    SiteSetting::setValue('sidebar_menu_config', json_encode($config), 'Applied default sidebar config', 'string');

    $mapRoleKeyToName = function ($key) {
        $k = strtolower(trim($key));
        return match ($k) {
            'super-admin' => 'Super Admin',
            'admin' => 'Admin',
            'operator' => 'Operator',
            'finance' => 'Finance',
            default => ucfirst($k),
        };
    };

    foreach ($config as $roleKey => $groups) {
        $roleName = $mapRoleKeyToName($roleKey);
        $role = Role::firstOrCreate(['name' => $roleName], ['guard_name' => 'web']);

        $permissionsToSync = [];
        foreach ($groups as $group) {
            $groupId = $group['id'] ?? null;
            if (! $groupId) continue;
            $groupPermName = "menu.{$groupId}";
            $permissionsToSync[] = $groupPermName;
            $groupPermission = Permission::firstOrNew(['name' => $groupPermName, 'guard_name' => 'web']);
            $groupPermission->display_name = $group['label'] ?? $groupId;
            $groupPermission->save();

            $items = $group['items'] ?? [];
            foreach ($items as $item) {
                $slug = preg_replace('/[^a-z0-9]+/i', '-', trim($item));
                $permName = "menu.{$groupId}.{$slug}";
                $permissionsToSync[] = $permName;
                $permission = Permission::firstOrNew(['name' => $permName, 'guard_name' => 'web']);
                $permission->display_name = $item;
                $permission->save();
            }
        }

        $role->syncPermissions(array_unique($permissionsToSync));
    }
});

echo "Default sidebar config applied and permissions synced.\n";
