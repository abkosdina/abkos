<?php

namespace Modules\Negotiation\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Modules\Negotiation\Actions\AcceptOfferAction;
use Modules\Negotiation\Actions\CancelNegotiationAction;
use Modules\Negotiation\Actions\ConvertNegotiationToOrderAction;
use Modules\Negotiation\Actions\CounterOfferAction;
use Modules\Negotiation\Actions\CreateNegotiationAction;
use Modules\Negotiation\Actions\CreateOfferAction;
use Modules\Negotiation\Actions\ExpireNegotiationAction;
use Modules\Negotiation\Actions\RejectOfferAction;
use Modules\Negotiation\Events\CounterOfferCreated;
use Modules\Negotiation\Events\NegotiationCancelled;
use Modules\Negotiation\Events\NegotiationCompleted;
use Modules\Negotiation\Events\NegotiationCreated;
use Modules\Negotiation\Events\NegotiationExpired;
use Modules\Negotiation\Events\OfferAccepted;
use Modules\Negotiation\Events\OfferCreated;
use Modules\Negotiation\Events\OfferRejected;
use Modules\Negotiation\Listeners\CreateActivityLogListener;
use Modules\Negotiation\Listeners\SendNotificationListener;
use Modules\Negotiation\Listeners\SendSmsListener;
use Modules\Negotiation\Repositories\Eloquent\NegotiationHistoryRepository;
use Modules\Negotiation\Repositories\Eloquent\NegotiationOfferRepository;
use Modules\Negotiation\Repositories\Eloquent\NegotiationRepository;
use Modules\Negotiation\Repositories\Interfaces\NegotiationHistoryRepositoryInterface;
use Modules\Negotiation\Repositories\Interfaces\NegotiationOfferRepositoryInterface;
use Modules\Negotiation\Repositories\Interfaces\NegotiationRepositoryInterface;
use Modules\Negotiation\Services\NegotiationOfferService;
use Modules\Negotiation\Services\NegotiationService;
use Modules\Negotiation\Services\NegotiationStateService;
use Modules\Negotiation\Services\NegotiationValidationService;
use Modules\Negotiation\Services\NegotiationWorkflowService;

class NegotiationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../Config/negotiation.php', 'negotiation');

        $this->app->bind(NegotiationRepositoryInterface::class, NegotiationRepository::class);
        $this->app->bind(NegotiationOfferRepositoryInterface::class, NegotiationOfferRepository::class);
        $this->app->bind(NegotiationHistoryRepositoryInterface::class, NegotiationHistoryRepository::class);

        $this->app->singleton(NegotiationValidationService::class, fn () => new NegotiationValidationService());
        $this->app->singleton(NegotiationStateService::class, fn () => new NegotiationStateService());
        $this->app->singleton(NegotiationWorkflowService::class, fn ($app) => new NegotiationWorkflowService(
            $app->make(NegotiationRepositoryInterface::class),
            $app->make(NegotiationOfferRepositoryInterface::class),
            $app->make(NegotiationHistoryRepositoryInterface::class),
            $app->make(NegotiationStateService::class),
        ));
        $this->app->singleton(NegotiationOfferService::class, fn ($app) => new NegotiationOfferService(
            $app->make(NegotiationOfferRepositoryInterface::class),
            $app->make(NegotiationRepositoryInterface::class),
            $app->make(NegotiationHistoryRepositoryInterface::class),
        ));
        $this->app->singleton(NegotiationService::class, fn ($app) => new NegotiationService(
            $app->make(NegotiationRepositoryInterface::class),
            $app->make(NegotiationOfferRepositoryInterface::class),
            $app->make(NegotiationHistoryRepositoryInterface::class),
            $app->make(NegotiationValidationService::class),
            $app->make(NegotiationWorkflowService::class),
            $app->make(NegotiationOfferService::class),
        ));

        $this->app->singleton(CreateNegotiationAction::class, fn ($app) => new CreateNegotiationAction($app->make(NegotiationService::class)));
        $this->app->singleton(CreateOfferAction::class, fn ($app) => new CreateOfferAction($app->make(NegotiationService::class)));
        $this->app->singleton(AcceptOfferAction::class, fn ($app) => new AcceptOfferAction($app->make(NegotiationService::class)));
        $this->app->singleton(RejectOfferAction::class, fn ($app) => new RejectOfferAction($app->make(NegotiationService::class)));
        $this->app->singleton(CounterOfferAction::class, fn ($app) => new CounterOfferAction($app->make(NegotiationService::class)));
        $this->app->singleton(CancelNegotiationAction::class, fn ($app) => new CancelNegotiationAction($app->make(NegotiationService::class)));
        $this->app->singleton(ExpireNegotiationAction::class, fn ($app) => new ExpireNegotiationAction($app->make(NegotiationService::class)));
        $this->app->singleton(ConvertNegotiationToOrderAction::class, fn ($app) => new ConvertNegotiationToOrderAction($app->make(NegotiationService::class)));
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../Routes/api.php');
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');

        Event::listen(NegotiationCreated::class, [CreateActivityLogListener::class, 'handle']);
        Event::listen(NegotiationCreated::class, SendNotificationListener::class);
        Event::listen(NegotiationCreated::class, SendSmsListener::class);

        Event::listen(OfferCreated::class, [CreateActivityLogListener::class, 'handle']);
        Event::listen(OfferAccepted::class, [CreateActivityLogListener::class, 'handle']);
        Event::listen(OfferRejected::class, [CreateActivityLogListener::class, 'handle']);
        Event::listen(CounterOfferCreated::class, [CreateActivityLogListener::class, 'handle']);
        Event::listen(NegotiationCancelled::class, [CreateActivityLogListener::class, 'handle']);
        Event::listen(NegotiationExpired::class, [CreateActivityLogListener::class, 'handle']);
        Event::listen(NegotiationCompleted::class, [CreateActivityLogListener::class, 'handle']);
    }
}
