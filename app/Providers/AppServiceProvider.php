<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Shared\Providers\SharedServiceProvider;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use App\Providers\WorkflowActionServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->register(SharedServiceProvider::class);
        $this->app->register(RepositoryServiceProvider::class);
        $this->app->register(\App\Providers\UserManagementServiceProvider::class);
        $this->app->register(\App\Providers\AuthenticationServiceProvider::class);
        $this->app->register(WorkflowActionServiceProvider::class);
        $this->app->register(\Modules\Workflow\Providers\WorkflowServiceProvider::class);
        $this->app->register(\Modules\Documents\Providers\DocumentsServiceProvider::class);
        $this->app->register(\Modules\KYC\Providers\KycServiceProvider::class);
        $this->app->register(\Modules\Ledger\Providers\LedgerServiceProvider::class);
        $this->app->register(\Modules\Wallet\Providers\WalletServiceProvider::class);
        $this->app->register(\Modules\Advertisements\Providers\AdvertisementsServiceProvider::class);
        $this->app->register(\Modules\Advertisements\Providers\AdvertisementWorkflowServiceProvider::class);
        $this->app->register(\Modules\Negotiation\Providers\NegotiationServiceProvider::class);
        $this->app->register(\Modules\Deals\Providers\DealsServiceProvider::class);
        $this->app->register(\Modules\Chat\Providers\ChatServiceProvider::class);
        $this->app->register(\Modules\Workflow\Providers\ApprovalServiceProvider::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $router = $this->app->make('router');
        $router->aliasMiddleware('permission', PermissionMiddleware::class);
        $router->aliasMiddleware('role', RoleMiddleware::class);
    }
}
