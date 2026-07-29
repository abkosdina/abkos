<?php

namespace Modules\Advertisements\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Event;
use App\Services\Workflow\WorkflowEngine;
use Modules\Advertisements\Adapters\AdvertisementWorkflowAdapter;
use Modules\Advertisements\Services\AdvertisementWorkflowService;
use Modules\Advertisements\Listeners\LogAdvertisementActivity;
use Modules\Advertisements\Listeners\RefreshAdvertisementCache;
use Modules\Advertisements\Listeners\RefreshSearchIndex;
use Modules\Advertisements\Events\AdvertisementSubmitted;
use Modules\Advertisements\Events\AdvertisementApproved;
use Modules\Advertisements\Events\AdvertisementRejected;
use Modules\Advertisements\Events\AdvertisementCorrectionRequested;
use Modules\Advertisements\Events\AdvertisementPublished;
use Modules\Advertisements\Events\AdvertisementPaused;
use Modules\Advertisements\Events\AdvertisementResumed;
use Modules\Advertisements\Events\AdvertisementArchived;
use Modules\Advertisements\Events\AdvertisementRestored;
use Modules\Advertisements\Events\AdvertisementExpired;
use Modules\Advertisements\Events\AdvertisementSold;

/**
 * Advertisements Workflow Service Provider
 *
 * Registers workflow services, configuration, routes, and event listeners.
 */
class AdvertisementWorkflowServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->registerConfiguration();
        $this->registerRoutes();
        $this->registerEventListeners();
        $this->registerMigrations();
        $this->registerPublishes();
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register generic advertisement workflow integration
        $this->app->singleton(AdvertisementWorkflowAdapter::class, function ($app) {
            return new AdvertisementWorkflowAdapter($app->make(WorkflowEngine::class));
        });

        $this->app->singleton(AdvertisementWorkflowService::class, function ($app) {
            return new AdvertisementWorkflowService($app->make(AdvertisementWorkflowAdapter::class));
        });
    }

    /**
     * Register configuration
     */
    protected function registerConfiguration(): void
    {
        $configPath = __DIR__ . '/../Config/AdvertisementWorkflow.php';

        $this->publishes([
            $configPath => config_path('advertisement-workflow.php'),
        ], 'advertisement-workflow-config');

        $this->mergeConfigFrom($configPath, 'advertisement-workflow');
    }

    /**
     * Register routes
     */
    protected function registerRoutes(): void
    {
        Route::middleware('api')
            ->prefix('api')
            ->group(__DIR__ . '/../Routes/workflow.php');
    }

    /**
     * Register event listeners
     */
    protected function registerEventListeners(): void
    {
        require_once __DIR__ . '/../Listeners/AdvertisementWorkflowListeners.php';

        $logActivity = new LogAdvertisementActivity();
        $refreshCache = new RefreshAdvertisementCache();
        $refreshSearch = new RefreshSearchIndex();

        // Register all workflow event listeners
        Event::listen(AdvertisementSubmitted::class, [$logActivity, 'handleSubmitted']);
        Event::listen(AdvertisementApproved::class, [$logActivity, 'handleApproved']);
        Event::listen(AdvertisementRejected::class, [$logActivity, 'handleRejected']);
        Event::listen(AdvertisementCorrectionRequested::class, [$logActivity, 'handleCorrectionRequested']);
        Event::listen(AdvertisementPublished::class, [$logActivity, 'handlePublished']);
        Event::listen(AdvertisementPaused::class, [$logActivity, 'handlePaused']);
        Event::listen(AdvertisementResumed::class, [$logActivity, 'handleResumed']);
        Event::listen(AdvertisementArchived::class, [$logActivity, 'handleArchived']);
        Event::listen(AdvertisementRestored::class, [$logActivity, 'handleRestored']);
        Event::listen(AdvertisementExpired::class, [$logActivity, 'handleExpired']);
        Event::listen(AdvertisementSold::class, [$logActivity, 'handleSold']);

        // Cache refresh listeners
        Event::listen(AdvertisementPublished::class, [$refreshCache, 'handle']);
        Event::listen(AdvertisementArchived::class, [$refreshCache, 'handle']);

        // Search index listeners
        if (config('advertisement-workflow.events.published')) {
            Event::listen(AdvertisementPublished::class, [$refreshSearch, 'handle']);
        }
    }

    /**
     * Register migrations
     */
    protected function registerMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../Database/migrations');
    }

    /**
     * Register publishable assets
     */
    protected function registerPublishes(): void
    {
        $this->publishes([
            __DIR__ . '/../Config/AdvertisementWorkflow.php' => config_path('advertisement-workflow.php'),
        ], 'advertisement-workflow-config');
    }
}
