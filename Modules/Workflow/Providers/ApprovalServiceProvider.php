<?php

namespace Modules\Workflow\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Workflow\Actions\ApproveAction;
use Modules\Workflow\Actions\CompleteApprovalAction;
use Modules\Workflow\Actions\RejectAction;
use Modules\Workflow\Actions\ReturnForCorrectionAction;
use Modules\Workflow\Actions\StartApprovalAction;
use Modules\Workflow\Interfaces\ApprovalEngineInterface;
use Modules\Workflow\Interfaces\ApproverResolverInterface;
use Modules\Workflow\Services\ApprovalAuthorizationService;
use Modules\Workflow\Services\ApprovalEngine;
use Modules\Workflow\Services\ApproverResolverRegistry;
use Modules\Workflow\Services\ConditionContextBuilder;
use Modules\Workflow\Services\ConditionEvaluationService;
use Modules\Workflow\Services\DefaultSelfApprovalRule;
use Modules\Workflow\Services\DynamicApproverResolver;
use Modules\Workflow\Services\PermissionApproverResolver;
use Modules\Workflow\Services\RoleApproverResolver;
use Modules\Workflow\Services\UserApproverResolver;

class ApprovalServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ApprovalEngineInterface::class, ApprovalEngine::class);
        $this->app->singleton(ApproverResolverRegistry::class, function ($app) {
            $registry = new ApproverResolverRegistry();
            $registry->register(new RoleApproverResolver());
            $registry->register(new PermissionApproverResolver());
            $registry->register(new UserApproverResolver());
            $registry->register(new DynamicApproverResolver());

            return $registry;
        });
        $this->app->singleton(ApprovalAuthorizationService::class, function ($app) {
            $service = new ApprovalAuthorizationService($app->make(ApproverResolverRegistry::class));
            $service->registerSelfApprovalRule(new DefaultSelfApprovalRule());

            return $service;
        });
        $this->app->singleton(StartApprovalAction::class, fn ($app) => new StartApprovalAction($app->make(ApprovalEngineInterface::class)));
        $this->app->singleton(ApproveAction::class, fn ($app) => new ApproveAction($app->make(ApprovalEngineInterface::class)));
        $this->app->singleton(RejectAction::class, fn ($app) => new RejectAction($app->make(ApprovalEngineInterface::class)));
        $this->app->singleton(ReturnForCorrectionAction::class, fn ($app) => new ReturnForCorrectionAction($app->make(ApprovalEngineInterface::class)));
        $this->app->singleton(CompleteApprovalAction::class, fn ($app) => new CompleteApprovalAction($app->make(ApprovalEngineInterface::class)));
    }
}
