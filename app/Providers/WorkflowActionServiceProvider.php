<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use App\Services\Workflow\ActionHandlerRegistry;
use App\Services\Workflow\ActionExecutionService;
use App\Services\Workflow\ActionContextBuilder;
use App\Services\Workflow\ActionTriggerResolver;
use App\Services\Workflow\ActionDispatcher;
use App\Services\Workflow\Handlers\SendNotificationActionHandler;
use App\Services\Workflow\Handlers\SendEmailActionHandler;
use App\Services\Workflow\Handlers\SendSmsActionHandler;
use App\Services\Workflow\Handlers\CreateEscrowActionHandler;
use App\Events\Workflow\WorkflowTransitioned;
use App\Events\Workflow\WorkflowCompleted;
use App\Events\Workflow\WorkflowCancelled;
use Modules\Workflow\Events\ApprovalApproved;
use Modules\Workflow\Events\ApprovalCompleted;
use Modules\Workflow\Events\ApprovalRejected;
use Modules\Workflow\Events\ApprovalReturnedForCorrection;

class WorkflowActionServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ActionHandlerRegistry::class, function () {
            $registry = new ActionHandlerRegistry();
            $registry->register('send_notification', new SendNotificationActionHandler());
            $registry->register('send_sms', new SendSmsActionHandler());
            $registry->register('send_email', new SendEmailActionHandler());
            $registry->register('create_escrow', new CreateEscrowActionHandler());

            return $registry;
        });

        $this->app->singleton(ActionContextBuilder::class, function () {
            return new ActionContextBuilder();
        });

        $this->app->singleton(ActionTriggerResolver::class, function () {
            return new ActionTriggerResolver();
        });

        $this->app->singleton(ActionExecutionService::class, function ($app) {
            return new ActionExecutionService(
                $app->make('db'),
                $app->make(ActionHandlerRegistry::class),
                $app->make(ActionContextBuilder::class)
            );
        });

        $this->app->singleton(ActionDispatcher::class, function ($app) {
            return new ActionDispatcher(
                $app->make(ActionTriggerResolver::class),
                $app->make(ActionExecutionService::class),
                $app->make(ActionContextBuilder::class)
            );
        });
    }

    public function boot(): void
    {
        $dispatcher = $this->app->make(ActionDispatcher::class);

        Event::listen(WorkflowTransitioned::class, [$dispatcher, 'handleWorkflowTransitioned']);
        Event::listen(WorkflowCompleted::class, [$dispatcher, 'handleWorkflowCompleted']);
        Event::listen(WorkflowCancelled::class, [$dispatcher, 'handleWorkflowCancelled']);
        Event::listen(ApprovalApproved::class, [$dispatcher, 'handleApprovalApproved']);
        Event::listen(ApprovalRejected::class, [$dispatcher, 'handleApprovalRejected']);
        Event::listen(ApprovalReturnedForCorrection::class, [$dispatcher, 'handleApprovalReturnedForCorrection']);
        Event::listen(ApprovalCompleted::class, [$dispatcher, 'handleApprovalCompleted']);
    }
}
