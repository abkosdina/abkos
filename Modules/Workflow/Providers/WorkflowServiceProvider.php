<?php

namespace Modules\Workflow\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Workflow\Repositories\Eloquent\WorkflowRepository;
use Modules\Workflow\Repositories\Eloquent\WorkflowInstanceRepository;
use Modules\Workflow\Repositories\Eloquent\WorkflowStepRepository;
use Modules\Workflow\Repositories\Eloquent\WorkflowApprovalRepository;
use Modules\Workflow\Repositories\Eloquent\WorkflowActionRepository;
use Modules\Workflow\Repositories\Eloquent\WorkflowTransitionRepository;
use Modules\Workflow\Repositories\Eloquent\WorkflowConditionRepository;
use Modules\Workflow\Repositories\Eloquent\WorkflowAssignmentRepository;
use Modules\Workflow\Repositories\Eloquent\WorkflowVersionRepository;
use Modules\Workflow\Repositories\Interfaces\WorkflowRepositoryInterface;
use Modules\Workflow\Repositories\Interfaces\WorkflowInstanceRepositoryInterface;
use Modules\Workflow\Repositories\Interfaces\WorkflowStepRepositoryInterface;
use Modules\Workflow\Repositories\Interfaces\WorkflowApprovalRepositoryInterface;
use Modules\Workflow\Repositories\Interfaces\WorkflowActionRepositoryInterface;
use Modules\Workflow\Repositories\Interfaces\WorkflowTransitionRepositoryInterface;
use Modules\Workflow\Repositories\Interfaces\WorkflowConditionRepositoryInterface;
use Modules\Workflow\Repositories\Interfaces\WorkflowAssignmentRepositoryInterface;
use Modules\Workflow\Repositories\Interfaces\WorkflowVersionRepositoryInterface;
use Modules\Workflow\Services\WorkflowService;
use Modules\Workflow\Services\WorkflowEngineService;
use Modules\Workflow\Services\WorkflowExecutionService;
use Modules\Workflow\Services\WorkflowApprovalService;
use Modules\Workflow\Services\WorkflowActionService;
use Modules\Workflow\Services\WorkflowConditionService;
use Modules\Workflow\Services\ConditionEvaluationService;
use Modules\Workflow\Services\ConditionEngineFacade;
use Modules\Workflow\Services\ConditionContextBuilder;
use Modules\Workflow\Services\ContextProviderRegistry;
use App\Repositories\Contracts\WorkflowDefinitionRepository;
use App\Repositories\Contracts\WorkflowInstanceRepository as GenericWorkflowInstanceRepository;
use App\Repositories\Contracts\WorkflowStateRepository;
use App\Repositories\Contracts\WorkflowTransitionRepository as GenericWorkflowTransitionRepository;
use App\Repositories\Contracts\WorkflowStepRepository as GenericWorkflowStepRepository;
use App\Repositories\Contracts\WorkflowIdempotencyRepository;
use App\Repositories\Eloquent\EloquentWorkflowDefinitionRepository;
use App\Repositories\Eloquent\EloquentWorkflowInstanceRepository;
use App\Repositories\Eloquent\EloquentWorkflowStateRepository;
use App\Repositories\Eloquent\EloquentWorkflowTransitionRepository;
use App\Repositories\Eloquent\EloquentWorkflowStepRepository;
use App\Repositories\Eloquent\EloquentWorkflowIdempotencyRepository;
use App\Services\Workflow\WorkflowEngine;
use App\Services\Workflow\WorkflowAuthorizationService;
use App\Services\Workflow\WorkflowLockingService;
use App\Services\Workflow\WorkflowIdempotencyService;

class WorkflowServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../Config/workflow.php', 'workflow');

        // ===== Old Workflow Module Bindings (Advertisement-specific) =====
        $this->app->bind(WorkflowRepositoryInterface::class, WorkflowRepository::class);
        $this->app->bind(WorkflowInstanceRepositoryInterface::class, WorkflowInstanceRepository::class);
        $this->app->bind(WorkflowStepRepositoryInterface::class, WorkflowStepRepository::class);
        $this->app->bind(WorkflowApprovalRepositoryInterface::class, WorkflowApprovalRepository::class);
        $this->app->bind(WorkflowActionRepositoryInterface::class, WorkflowActionRepository::class);
        $this->app->bind(WorkflowTransitionRepositoryInterface::class, WorkflowTransitionRepository::class);
        $this->app->bind(WorkflowConditionRepositoryInterface::class, WorkflowConditionRepository::class);
        $this->app->bind(WorkflowAssignmentRepositoryInterface::class, WorkflowAssignmentRepository::class);
        $this->app->bind(WorkflowVersionRepositoryInterface::class, WorkflowVersionRepository::class);

        $this->app->singleton(WorkflowService::class, fn ($app) => new WorkflowService($app->make(WorkflowRepositoryInterface::class), $app->make(WorkflowVersionRepositoryInterface::class)));
        $this->app->singleton(WorkflowEngineService::class, fn ($app) => new WorkflowEngineService(
            $app->make(WorkflowInstanceRepositoryInterface::class),
            $app->make(WorkflowStepRepositoryInterface::class),
            $app->make(WorkflowTransitionRepositoryInterface::class),
            $app->make(WorkflowConditionRepositoryInterface::class),
            $app->make(WorkflowActionService::class)
        ));
        $this->app->singleton(WorkflowExecutionService::class, fn ($app) => new WorkflowExecutionService(
            $app->make(WorkflowInstanceRepositoryInterface::class),
            $app->make(WorkflowStepRepositoryInterface::class),
            $app->make(WorkflowApprovalService::class),
            $app->make(WorkflowActionService::class)
        ));
        $this->app->singleton(WorkflowApprovalService::class, fn ($app) => new WorkflowApprovalService(
            $app->make(WorkflowApprovalRepositoryInterface::class),
            $app->make(WorkflowInstanceRepositoryInterface::class),
            $app->make(WorkflowEngineService::class)
        ));
        $this->app->singleton(WorkflowActionService::class, fn ($app) => new WorkflowActionService(
            $app->make(WorkflowActionRepositoryInterface::class)
        ));
        $this->app->singleton(WorkflowConditionService::class, fn ($app) => new WorkflowConditionService(
            $app->make(WorkflowConditionRepositoryInterface::class)
        ));
        $this->app->singleton(ConditionEvaluationService::class, fn () => new ConditionEvaluationService());
        $this->app->singleton(ConditionContextBuilder::class, fn () => new ConditionContextBuilder());
        $this->app->singleton(ContextProviderRegistry::class, fn () => new ContextProviderRegistry());
        $this->app->singleton(ConditionEngineFacade::class, fn ($app) => new ConditionEngineFacade(
            $app->make(ConditionEvaluationService::class)
        ));

        // ===== New Generic Workflow Engine (Phase 1) =====
        // Generic repository bindings
        $this->app->bind(
            WorkflowDefinitionRepository::class,
            EloquentWorkflowDefinitionRepository::class
        );

        $this->app->bind(
            GenericWorkflowInstanceRepository::class,
            EloquentWorkflowInstanceRepository::class
        );

        $this->app->bind(
            WorkflowStateRepository::class,
            EloquentWorkflowStateRepository::class
        );

        $this->app->bind(
            GenericWorkflowTransitionRepository::class,
            EloquentWorkflowTransitionRepository::class
        );

        $this->app->bind(
            GenericWorkflowStepRepository::class,
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

        // Register the core generic WorkflowEngine
        $this->app->singleton(WorkflowEngine::class, function ($app) {
            return new WorkflowEngine(
                $app->make(WorkflowDefinitionRepository::class),
                $app->make(GenericWorkflowInstanceRepository::class),
                $app->make(WorkflowStateRepository::class),
                $app->make(GenericWorkflowTransitionRepository::class),
                $app->make(GenericWorkflowStepRepository::class),
                $app->make(WorkflowIdempotencyRepository::class),
                $app->make(WorkflowAuthorizationService::class),
                $app->make(WorkflowLockingService::class),
                $app->make(WorkflowIdempotencyService::class),
                $app->make(ConditionEvaluationService::class),
                $app->make(ConditionContextBuilder::class)
            );
        });
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../Routes/api.php');
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
    }
}
