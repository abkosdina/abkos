<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('roles')) {
            return;
        }

        $mappings = [
            'administrator' => 'مدیر-سیستم',
            'operator' => 'اپراتور',
            'bank_employee' => 'کارمند-بانک',
            'customer' => 'مشتری',
            'Admin' => 'مدیر',
            'Super Admin' => 'مدیر-کل',
            'Finance' => 'مالی',
            'User' => 'کاربر',
            'Operator' => 'اپراتور',
            'Senior Operator' => 'اپراتور-ارشد',
            'Bank Employee' => 'کارمند-بانک',
            'Customer' => 'مشتری',
        ];

        foreach ($mappings as $roleName => $slugFa) {
            DB::table('roles')
                ->where('name', $roleName)
                ->whereNull('slug_fa')
                ->update(['slug_fa' => $slugFa]);
        }

        $roles = DB::table('roles')
            ->whereNull('slug_fa')
            ->whereNotNull('display_name')
            ->get(['id', 'display_name']);

        foreach ($roles as $role) {
            $slug = strtolower(trim(preg_replace('/[^\p{L}\p{N}]+/u', '-', $role->display_name), '-'));
            if ($slug !== '') {
                DB::table('roles')
                    ->where('id', $role->id)
                    ->update(['slug_fa' => $slug]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('roles')) {
            return;
        }

        $roleNames = array_values([
            'administrator',
            'operator',
            'bank_employee',
            'customer',
            'Admin',
            'Super Admin',
            'Finance',
            'User',
            'Operator',
            'Senior Operator',
            'Bank Employee',
            'Customer',
        ]);

        DB::table('roles')
            ->whereIn('name', $roleNames)
            ->whereIn('slug_fa', [
                'مدیر-سیستم',
                'اپراتور',
                'کارمند-بانک',
                'مشتری',
                'مدیر',
                'مدیر-کل',
                'مالی',
                'کاربر',
                'اپراتور-ارشد',
            ])
            ->update(['slug_fa' => null]);
    }
};
