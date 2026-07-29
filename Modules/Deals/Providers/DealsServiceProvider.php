<?php

namespace Modules\Deals\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use App\Services\Workflow\WorkflowEngine;
use Modules\Deals\Listeners\CreateDealFromNegotiationListener;
use Modules\Deals\Repositories\Eloquent\DealRepository;
use Modules\Deals\Repositories\Interfaces\DealRepositoryInterface;
use Modules\Deals\Services\DealService;
use Modules\Deals\Services\DealValidationService;
use Modules\Deals\Services\DealWorkflowService;
use Modules\KYC\Services\KycAccessService;
use Modules\Negotiation\Events\NegotiationCompleted;

class DealsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(DealRepositoryInterface::class, DealRepository::class);

        $this->app->singleton(DealValidationService::class, function ($app) {
            return new DealValidationService(
                $app->make(KycAccessService::class),
                $app->make(DealRepositoryInterface::class)
            );
        });

        $this->app->singleton(DealWorkflowService::class, function ($app) {
            return new DealWorkflowService($app->make(WorkflowEngine::class));
        });

        $this->app->singleton(DealService::class, function ($app) {
            return new DealService(
                $app->make(DealRepositoryInterface::class),
                $app->make(DealValidationService::class),
                $app->make(DealWorkflowService::class)
            );
        });

        $this->app->singleton(CreateDealFromNegotiationListener::class, function ($app) {
            return new CreateDealFromNegotiationListener(
                $app->make(DealService::class)
            );
        });
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../Routes/api.php');
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');

        Event::listen(NegotiationCompleted::class, [CreateDealFromNegotiationListener::class, 'handle']);
    }
}
