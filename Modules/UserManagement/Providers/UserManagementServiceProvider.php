<?php

namespace Modules\UserManagement\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\UserManagement\Repositories\Eloquent\UserRepository;
use Modules\UserManagement\Repositories\Interfaces\UserRepositoryInterface;
use Modules\UserManagement\Repositories\Eloquent\ProfileRepository;
use Modules\UserManagement\Repositories\Interfaces\ProfileRepositoryInterface;
use Modules\UserManagement\Repositories\Eloquent\RoleRepository;
use Modules\UserManagement\Repositories\Interfaces\RoleRepositoryInterface;
use Modules\UserManagement\Repositories\Eloquent\PermissionRepository;
use Modules\UserManagement\Repositories\Interfaces\PermissionRepositoryInterface;
use Modules\UserManagement\Repositories\Eloquent\BankAccountRepository;
use Modules\UserManagement\Repositories\Interfaces\BankAccountRepositoryInterface;
use Modules\UserManagement\Services\UserService;
use Modules\UserManagement\Services\ProfileService;
use Modules\UserManagement\Services\RoleService;
use Modules\UserManagement\Services\PermissionService;
use Modules\UserManagement\Services\BankAccountService;

class UserManagementServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../Config/user-management.php', 'user-management');

        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(ProfileRepositoryInterface::class, ProfileRepository::class);
        $this->app->bind(RoleRepositoryInterface::class, RoleRepository::class);
        $this->app->bind(PermissionRepositoryInterface::class, PermissionRepository::class);
        $this->app->bind(BankAccountRepositoryInterface::class, BankAccountRepository::class);

        $this->app->singleton(UserService::class, fn ($app) => new UserService($app->make(UserRepositoryInterface::class)));
        $this->app->singleton(ProfileService::class, fn ($app) => new ProfileService($app->make(ProfileRepositoryInterface::class)));
        $this->app->singleton(RoleService::class, fn ($app) => new RoleService($app->make(RoleRepositoryInterface::class)));
        $this->app->singleton(PermissionService::class, fn ($app) => new PermissionService($app->make(PermissionRepositoryInterface::class)));
        $this->app->singleton(BankAccountService::class, fn ($app) => new BankAccountService($app->make(BankAccountRepositoryInterface::class)));
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../Routes/api.php');
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
    }
}
