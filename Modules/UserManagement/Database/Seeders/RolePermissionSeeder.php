<?php

namespace Modules\UserManagement\Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['name' => 'administrator', 'display_name' => 'مدیر سیستم', 'slug_fa' => 'مدیر-سیستم'],
            ['name' => 'Super Admin', 'display_name' => 'مدیر کل', 'slug_fa' => 'مدیر-کل'],
            ['name' => 'operator', 'display_name' => 'اپراتور', 'slug_fa' => 'اپراتور'],
            ['name' => 'bank_employee', 'display_name' => 'کارمند بانک', 'slug_fa' => 'کارمند-بانک'],
            ['name' => 'customer', 'display_name' => 'مشتری', 'slug_fa' => 'مشتری'],
        ];

        foreach ($roles as $role) {
            $roleModel = Role::firstOrCreate([
                'name' => $role['name'],
                'guard_name' => 'web',
            ], [
                'display_name' => $role['display_name'],
                'slug_fa' => $role['slug_fa'],
            ]);

            if ($roleModel->display_name !== $role['display_name'] || $roleModel->slug_fa !== $role['slug_fa']) {
                $roleModel->display_name = $role['display_name'];
                $roleModel->slug_fa = $role['slug_fa'];
                $roleModel->save();
            }
        }

        $permissions = [
            'menu.users' => ['display_name' => 'مدیریت کاربران', 'slug_fa' => 'مدیریت-کاربران'],
            'users.view' => ['display_name' => 'مشاهده کاربران', 'slug_fa' => 'مشاهده-کاربران'],
            'users.create' => ['display_name' => 'ایجاد کاربر', 'slug_fa' => 'ایجاد-کاربر'],
            'users.update' => ['display_name' => 'ویرایش کاربر', 'slug_fa' => 'ویرایش-کاربر'],
            'users.delete' => ['display_name' => 'حذف کاربر', 'slug_fa' => 'حذف-کاربر'],
            'roles.manage' => ['display_name' => 'مدیریت نقش‌ها', 'slug_fa' => 'مدیریت-نقش‌ها'],
            'permissions.manage' => ['display_name' => 'مدیریت دسترسی‌ها', 'slug_fa' => 'مدیریت-دسترسی‌ها'],
            'bank-accounts.manage' => ['display_name' => 'مدیریت حساب‌های بانکی', 'slug_fa' => 'مدیریت-حساب‌های-بانکی'],
            'menu.deals' => ['display_name' => 'مدیریت معاملات', 'slug_fa' => 'مدیریت-معاملات'],
        ];

        foreach ($permissions as $permission => $meta) {
            $permissionModel = Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ], [
                'display_name' => $meta['display_name'],
                'slug_fa' => $meta['slug_fa'],
            ]);

            if ($permissionModel->display_name !== $meta['display_name'] || $permissionModel->slug_fa !== $meta['slug_fa']) {
                $permissionModel->display_name = $meta['display_name'];
                $permissionModel->slug_fa = $meta['slug_fa'];
                $permissionModel->save();
            }
        }

        $admin = Role::findByName('administrator', 'web');
        $admin->syncPermissions(Permission::all());

        $superAdmin = Role::firstOrCreate([
            'name' => 'Super Admin',
            'guard_name' => 'web',
        ], [
            'display_name' => 'مدیر کل',
            'slug_fa' => 'مدیر-کل',
        ]);
        $superAdmin->syncPermissions(Permission::all());
    }
}
