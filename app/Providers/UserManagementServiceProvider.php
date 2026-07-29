<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\UserManagement\Providers\UserManagementServiceProvider as ModuleUserManagementServiceProvider;

class UserManagementServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->register(ModuleUserManagementServiceProvider::class);
    }
}
