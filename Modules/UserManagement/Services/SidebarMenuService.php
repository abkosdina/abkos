<?php

namespace Modules\UserManagement\Services;

use App\Models\SiteSetting;
use Spatie\Permission\Models\Role;

class SidebarMenuService
{
    /**
     * Get default sidebar menu structure for all roles.
     */
    public static function getDefaultMenus(): array
    {
        return [
            'super-admin' => [
                ['id' => 'dashboard', 'icon' => '🏠', 'label' => 'داشبورد', 'items' => ['نمای کلی', 'آمار و گزارش', 'فعالیت‌های اخیر']],
                ['id' => 'users', 'icon' => '👥', 'label' => 'کاربران', 'items' => ['کاربران']],
                ['id' => 'ads', 'icon' => '📢', 'label' => 'آگهی‌ها', 'items' => ['همه آگهی‌ها', 'تأیید آگهی‌ها', 'آگهی‌های معلق']],
                ['id' => 'orders', 'icon' => '📦', 'label' => 'سفارش‌ها', 'items' => ['همه سفارش‌ها', 'در حال انجام', 'تکمیل‌شده']],
                ['id' => 'wallet', 'icon' => '💰', 'label' => 'کیف پول', 'items' => ['تراکنش‌ها', 'درخواست‌های برداشت', 'اعتبار']],
                ['id' => 'messages', 'icon' => '💬', 'label' => 'پیام‌ها', 'items' => ['گفتگوها', 'آرشیو پیام‌ها']],
                ['id' => 'support', 'icon' => '❓', 'label' => 'پشتیبانی', 'items' => ['تیکت‌ها', 'سوالات متداول']],
            ],
            'admin' => [
                ['id' => 'dashboard', 'icon' => '🏠', 'label' => 'داشبورد', 'items' => ['نمای کلی', 'آمار و گزارش', 'فعالیت‌های اخیر']],
                ['id' => 'ads', 'icon' => '📢', 'label' => 'آگهی‌ها', 'items' => ['همه آگهی‌ها', 'تأیید آگهی‌ها', 'آگهی‌های معلق']],
                ['id' => 'orders', 'icon' => '📦', 'label' => 'سفارش‌ها', 'items' => ['همه سفارش‌ها', 'در حال انجام', 'تکمیل‌شده']],
                ['id' => 'wallet', 'icon' => '💰', 'label' => 'کیف پول', 'items' => ['تراکنش‌ها', 'درخواست‌های برداشت', 'اعتبار']],
                ['id' => 'messages', 'icon' => '💬', 'label' => 'پیام‌ها', 'items' => ['گفتگوها', 'آرشیو پیام‌ها']],
                ['id' => 'support', 'icon' => '❓', 'label' => 'پشتیبانی', 'items' => ['تیکت‌ها', 'سوالات متداول']],
            ],
            'administrator' => [
                ['id' => 'dashboard', 'icon' => '🏠', 'label' => 'داشبورد', 'items' => ['نمای کلی', 'آمار و گزارش', 'فعالیت‌های اخیر']],
                ['id' => 'ads', 'icon' => '📢', 'label' => 'آگهی‌ها', 'items' => ['همه آگهی‌ها', 'تأیید آگهی‌ها', 'آگهی‌های معلق']],
                ['id' => 'orders', 'icon' => '📦', 'label' => 'سفارش‌ها', 'items' => ['همه سفارش‌ها', 'در حال انجام', 'تکمیل‌شده']],
                ['id' => 'wallet', 'icon' => '💰', 'label' => 'کیف پول', 'items' => ['تراکنش‌ها', 'درخواست‌های برداشت', 'اعتبار']],
                ['id' => 'messages', 'icon' => '💬', 'label' => 'پیام‌ها', 'items' => ['گفتگوها', 'آرشیو پیام‌ها']],
                ['id' => 'support', 'icon' => '❓', 'label' => 'پشتیبانی', 'items' => ['تیکت‌ها', 'سوالات متداول']],
            ],
            'operator' => [
                ['id' => 'dashboard', 'icon' => '🏠', 'label' => 'داشبورد', 'items' => ['نمای کلی', 'فعالیت‌های اخیر']],
                ['id' => 'ads', 'icon' => '📢', 'label' => 'آگهی‌ها', 'items' => ['همه آگهی‌ها', 'تأیید آگهی‌ها']],
                ['id' => 'messages', 'icon' => '💬', 'label' => 'پیام‌ها', 'items' => ['گفتگوها', 'آرشیو پیام‌ها']],
                ['id' => 'support', 'icon' => '❓', 'label' => 'پشتیبانی', 'items' => ['تیکت‌ها']],
            ],
            'bank-employee' => [
                ['id' => 'dashboard', 'icon' => '🏠', 'label' => 'داشبورد', 'items' => ['نمای کلی', 'فعالیت‌های اخیر']],
                ['id' => 'ads', 'icon' => '📢', 'label' => 'آگهی‌ها', 'items' => ['همه آگهی‌ها', 'تأیید آگهی‌ها']],
                ['id' => 'messages', 'icon' => '💬', 'label' => 'پیام‌ها', 'items' => ['گفتگوها', 'آرشیو پیام‌ها']],
                ['id' => 'support', 'icon' => '❓', 'label' => 'پشتیبانی', 'items' => ['تیکت‌ها']],
            ],
            'finance' => [
                ['id' => 'dashboard', 'icon' => '🏠', 'label' => 'داشبورد', 'items' => ['نمای کلی', 'آمار و گزارش']],
                ['id' => 'orders', 'icon' => '📦', 'label' => 'سفارش‌ها', 'items' => ['همه سفارش‌ها', 'در حال انجام']],
                ['id' => 'wallet', 'icon' => '💰', 'label' => 'کیف پول', 'items' => ['تراکنش‌ها', 'درخواست‌های برداشت']],
                ['id' => 'contracts', 'icon' => '📄', 'label' => 'قراردادها', 'items' => ['قراردادهای فعال', 'قراردادهای تکمیل‌شده']],
                ['id' => 'support', 'icon' => '❓', 'label' => 'پشتیبانی', 'items' => ['تیکت‌ها']],
            ],
            'customer' => [
                ['id' => 'dashboard', 'icon' => '🏠', 'label' => 'داشبورد', 'items' => ['نمای کلی', 'فعالیت‌های اخیر', 'آمار حساب']],
                ['id' => 'account', 'icon' => '👤', 'label' => 'حساب کاربری', 'items' => ['پروفایل', 'اطلاعات شخصی', 'حساب‌های بانکی', 'تنظیمات حساب', 'امنیت حساب']],
                ['id' => 'kyc', 'icon' => '🪪', 'label' => 'احراز هویت', 'items' => ['وضعیت احراز هویت', 'ارسال مدارک', 'امضا و اقرارنامه', 'تاریخچه بررسی']],
                ['id' => 'ads', 'icon' => '📢', 'label' => 'آگهی‌ها', 'items' => ['همه آگهی‌های من', 'ثبت آگهی جدید', 'پیش‌نویس‌ها', 'آگهی‌های فعال', 'آگهی‌های آرشیو شده']],
                ['id' => 'search', 'icon' => '🔍', 'label' => 'جستجوی آگهی', 'items' => ['همه آگهی‌ها', 'علاقه‌مندی‌ها', 'مقایسه آگهی‌ها']],
                ['id' => 'negotiations', 'icon' => '🤝', 'label' => 'مذاکرات', 'items' => ['مذاکرات دریافتی', 'مذاکرات ارسالی', 'پیشنهادهای قیمت']],
                ['id' => 'orders', 'icon' => '📦', 'label' => 'سفارش‌ها', 'items' => ['سفارش‌های خرید', 'سفارش‌های فروش', 'سفارش‌های در حال انجام', 'سفارش‌های تکمیل‌شده', 'سفارش‌های لغوشده']],
                ['id' => 'wallet', 'icon' => '💰', 'label' => 'کیف پول', 'items' => ['موجودی', 'تراکنش‌ها', 'واریز', 'برداشت', 'گزارش مالی']],
                ['id' => 'escrow', 'icon' => '🔒', 'label' => 'معاملات امانی', 'items' => ['معاملات فعال', 'تاریخچه معاملات', 'وضعیت آزادسازی وجه']],
                ['id' => 'contracts', 'icon' => '📄', 'label' => 'قراردادها', 'items' => ['قراردادهای فعال', 'قراردادهای تکمیل‌شده', 'دانلود قراردادها']],
                ['id' => 'messages', 'icon' => '💬', 'label' => 'پیام‌ها', 'items' => ['گفتگوها', 'فایل‌های ارسالی', 'آرشیو پیام‌ها']],
                ['id' => 'ratings', 'icon' => '⭐', 'label' => 'امتیازات', 'items' => ['امتیاز من', 'نظرات کاربران', 'تاریخچه امتیازها']],
                ['id' => 'disputes', 'icon' => '⚖', 'label' => 'شکایات و داوری', 'items' => ['ثبت شکایت', 'شکایت‌های من', 'پرونده‌های داوری']],
                ['id' => 'documents', 'icon' => '📁', 'label' => 'اسناد', 'items' => ['مدارک من', 'فایل‌های بارگذاری‌شده', 'قراردادها']],
                ['id' => 'notifications', 'icon' => '🔔', 'label' => 'اعلان‌ها', 'items' => ['اعلان‌های سیستم', 'پیامک‌ها', 'اطلاعیه‌ها']],
                ['id' => 'support', 'icon' => '❓', 'label' => 'پشتیبانی', 'items' => ['ثبت تیکت', 'تیکت‌های من', 'سوالات متداول']],
            ],
            'user' => [
                ['id' => 'dashboard', 'icon' => '🏠', 'label' => 'داشبورد', 'items' => ['نمای کلی', 'فعالیت‌های اخیر', 'آمار حساب']],
                ['id' => 'account', 'icon' => '👤', 'label' => 'حساب کاربری', 'items' => ['پروفایل', 'اطلاعات شخصی', 'حساب‌های بانکی', 'تنظیمات حساب', 'امنیت حساب']],
                ['id' => 'kyc', 'icon' => '🪪', 'label' => 'احراز هویت', 'items' => ['وضعیت احراز هویت', 'ارسال مدارک', 'امضا و اقرارنامه', 'تاریخچه بررسی']],
                ['id' => 'ads', 'icon' => '📢', 'label' => 'آگهی‌ها', 'items' => ['همه آگهی‌های من', 'ثبت آگهی جدید', 'پیش‌نویس‌ها', 'آگهی‌های فعال', 'آگهی‌های آرشیو شده']],
                ['id' => 'search', 'icon' => '🔍', 'label' => 'جستجوی آگهی', 'items' => ['همه آگهی‌ها', 'علاقه‌مندی‌ها', 'مقایسه آگهی‌ها']],
                ['id' => 'negotiations', 'icon' => '🤝', 'label' => 'مذاکرات', 'items' => ['مذاکرات دریافتی', 'مذاکرات ارسالی', 'پیشنهادهای قیمت']],
                ['id' => 'orders', 'icon' => '📦', 'label' => 'سفارش‌ها', 'items' => ['سفارش‌های خرید', 'سفارش‌های فروش', 'سفارش‌های در حال انجام', 'سفارش‌های تکمیل‌شده', 'سفارش‌های لغوشده']],
                ['id' => 'wallet', 'icon' => '💰', 'label' => 'کیف پول', 'items' => ['موجودی', 'تراکنش‌ها', 'واریز', 'برداشت', 'گزارش مالی']],
                ['id' => 'escrow', 'icon' => '🔒', 'label' => 'معاملات امانی', 'items' => ['معاملات فعال', 'تاریخچه معاملات', 'وضعیت آزادسازی وجه']],
                ['id' => 'contracts', 'icon' => '📄', 'label' => 'قراردادها', 'items' => ['قراردادهای فعال', 'قراردادهای تکمیل‌شده', 'دانلود قراردادها']],
                ['id' => 'messages', 'icon' => '💬', 'label' => 'پیام‌ها', 'items' => ['گفتگوها', 'فایل‌های ارسالی', 'آرشیو پیام‌ها']],
                ['id' => 'ratings', 'icon' => '⭐', 'label' => 'امتیازات', 'items' => ['امتیاز من', 'نظرات کاربران', 'تاریخچه امتیازها']],
                ['id' => 'disputes', 'icon' => '⚖', 'label' => 'شکایات و داوری', 'items' => ['ثبت شکایت', 'شکایت‌های من', 'پرونده‌های داوری']],
                ['id' => 'documents', 'icon' => '📁', 'label' => 'اسناد', 'items' => ['مدارک من', 'فایل‌های بارگذاری‌شده', 'قراردادها']],
                ['id' => 'notifications', 'icon' => '🔔', 'label' => 'اعلان‌ها', 'items' => ['اعلان‌های سیستم', 'پیامک‌ها', 'اطلاعیه‌ها']],
                ['id' => 'support', 'icon' => '❓', 'label' => 'پشتیبانی', 'items' => ['ثبت تیکت', 'تیکت‌های من', 'سوالات متداول']],
            ],
        ];
    }

    /**
     * Get base menus for a specific role.
     */
    public static function getBaseMenusForRole(string $roleKey): array
    {
        $allMenus = self::getDefaultMenus();
        return $allMenus[$roleKey] ?? $allMenus['user'] ?? [];
    }

    /**
     * Map role names to canonical sidebar role keys.
     */
    public static function getCanonicalRoleKey(string $roleName): string
    {
        $normalized = strtolower(trim($roleName));
        return match ($normalized) {
            'super admin' => 'super-admin',
            'administrator', 'admin' => 'admin',
            'bank_employee', 'bank-employee' => 'bank-employee',
            'customer', 'user' => 'customer',
            'finance' => 'finance',
            'operator', 'senior-operator', 'senior operator' => 'operator',
            default => $normalized,
        };
    }

    /**
     * Get all available role keys with their base menus.
     */
    public static function getAllRoleMenus(): array
    {
        return self::getDefaultMenus();
    }

    /**
     * Initialize sidebar config with default menus for all roles if not already set.
     */
    public static function ensureDefaultsExist(): void
    {
        $config = SiteSetting::getValue('sidebar_menu_config', null);
        
        if (! $config || (is_string($config) && trim($config) === '') || (is_array($config) && empty($config))) {
            $defaults = self::getDefaultMenus();
            SiteSetting::setValue('sidebar_menu_config', json_encode($defaults), 'Default sidebar menu configuration', 'string');
        }
    }
}
