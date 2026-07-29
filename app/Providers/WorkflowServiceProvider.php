<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\Contracts\WorkflowDefinitionRepository;
use App\Repositories\Contracts\WorkflowInstanceRepository;
use App\Repositories\Contracts\WorkflowStateRepository;
use App\Repositories\Contracts\WorkflowTransitionRepository;
use App\Repositories\Contracts\WorkflowStepRepository;
use App\Repositories\Contracts\WorkflowIdempotencyRepository;
use App\Repositories\Eloquent\EloquentWorkflowDefinitionRepository;
use App\Repositories\Eloquent\EloquentWorkflowInstanceRepository;
use App\Repositories\Eloquent\EloquentWorkflowStateRepository;
use App\Repositories\Eloquent\EloquentWorkflowTransitionRepository;
use App\Repositories\Eloquent\EloquentWorkflowStepRepository;
use App\Repositories\Eloquent\EloquentWorkflowIdempotencyRepository;
use App\Services\Workflow\WorkflowDefinitionService;
use App\Services\Workflow\WorkflowEngine;
use App\Services\Workflow\WorkflowAuthorizationService;
use App\Services\Workflow\WorkflowInstanceService;
use App\Services\Workflow\WorkflowLockingService;
use App\Services\Workflow\WorkflowIdempotencyService;
use App\Services\Workflow\WorkflowTransitionService;

/**
 * WorkflowServiceProvider
 * 
 * Registers all generic workflow services and repositories.
 * This provider makes the workflow engine available application-wide.
 */
class WorkflowServiceProvider extends ServiceProvider
{
    /**
     * Register services in the container
     */
    public function register(): void
    {
        // Register repository bindings
        $this->app->bind(
            WorkflowDefinitionRepository::class,
            EloquentWorkflowDefinitionRepository::class
        );

        $this->app->bind(
            WorkflowInstanceRepository::class,
            EloquentWorkflowInstanceRepository::class
        );

        $this->app->bind(
            WorkflowStateRepository::class,
            EloquentWorkflowStateRepository::class
        );

        $this->app->bind(
            WorkflowTransitionRepository::class,
            EloquentWorkflowTransitionRepository::class
        );

        $this->app->bind(
            WorkflowStepRepository::class,
            EloquentWorkflowStepRepository::class
        );

        $this->app->bind(
            WorkflowIdempotencyRepository::class,
            EloquentWorkflowIdempotencyRepository::class
        );

        // Register supporting services
        $this->app->singleton(WorkflowAuthorizationService::class, function ($app) {
            return new WorkflowAuthorizationService();
        });

        $this->app->singleton(WorkflowLockingService::class, function ($app) {
            return new WorkflowLockingService();
        });

        $this->app->singleton(WorkflowIdempotencyService::class, function ($app) {
            return new WorkflowIdempotencyService(
                $app->make(WorkflowIdempotencyRepository::class)
            );
        });

        $this->app->singleton(WorkflowDefinitionService::class, function ($app) {
            return new WorkflowDefinitionService($app->make(WorkflowDefinitionRepository::class));
        });

        $this->app->singleton(WorkflowInstanceService::class, function ($app) {
            return new WorkflowInstanceService($app->make(WorkflowInstanceRepository::class));
        });

        $this->app->singleton(WorkflowTransitionService::class, function ($app) {
            return new WorkflowTransitionService($app->make(WorkflowTransitionRepository::class));
        });

        // Register the core WorkflowEngine
        $this->app->singleton(WorkflowEngine::class, function ($app) {
            return new WorkflowEngine(
                $app->make(WorkflowDefinitionRepository::class),
                $app->make(WorkflowInstanceRepository::class),
                $app->make(WorkflowStateRepository::class),
                $app->make(WorkflowTransitionRepository::class),
                $app->make(WorkflowStepRepository::class),
                $app->make(WorkflowIdempotencyRepository::class),
                $app->make(WorkflowAuthorizationService::class),
                $app->make(WorkflowLockingService::class),
                $app->make(WorkflowIdempotencyService::class)
            );
        });
    }

    /**
     * Bootstrap services
     */
    public function boot(): void
    {
        //
    }
}
