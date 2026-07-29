<?php

namespace Modules\KYC\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\KYC\Repositories\Eloquent\KycProfileRepository;
use Modules\KYC\Repositories\Interfaces\KycProfileRepositoryInterface;
use Modules\KYC\Repositories\Eloquent\KycRequestRepository;
use Modules\KYC\Repositories\Interfaces\KycRequestRepositoryInterface;
use Modules\KYC\Services\KycService;
use Modules\KYC\Services\KycVerificationService;
use Modules\KYC\Services\KycAccessService;

class KycServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../Config/kyc.php', 'kyc');

        $this->app->bind(KycRequestRepositoryInterface::class, KycRequestRepository::class);
        $this->app->bind(KycProfileRepositoryInterface::class, KycProfileRepository::class);

        $this->app->singleton(KycService::class, fn ($app) => new KycService(
            $app->make(KycRequestRepositoryInterface::class),
            $app->make(KycProfileRepositoryInterface::class)
        ));

        // Register KYC Verification Service
        $this->app->singleton(\Modules\KYC\Services\KycVerificationService::class, fn ($app) => new \Modules\KYC\Services\KycVerificationService(
            $app->make(KycRequestRepositoryInterface::class),
            $app->make(KycProfileRepositoryInterface::class),
            $app->make(\Modules\Documents\Services\StorageService::class),
            $app->make(\Modules\Documents\Services\HashService::class),
            $app->make(\App\Services\Workflow\WorkflowEngine::class),
            $app->make(\Modules\Workflow\Interfaces\ApprovalEngineInterface::class),
        ));

        // Register KYC Access Service
        $this->app->singleton(\Modules\KYC\Services\KycAccessService::class, fn ($app) => new \Modules\KYC\Services\KycAccessService());
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../Routes/api.php');
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
    }
}
