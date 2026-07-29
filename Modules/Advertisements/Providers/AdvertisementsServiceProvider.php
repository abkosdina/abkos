<?php

namespace Modules\Advertisements\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Modules\Advertisements\Actions\ArchiveAdvertisementAction;
use Modules\Advertisements\Actions\CreateAdvertisementAction;
use Modules\Advertisements\Actions\DeleteAdvertisementAction;
use Modules\Advertisements\Actions\ApproveAdvertisementAction;
use Modules\Advertisements\Actions\PauseAdvertisementAction;
use Modules\Advertisements\Actions\PublishAdvertisementAction;
use Modules\Advertisements\Actions\RejectAdvertisementAction;
use Modules\Advertisements\Actions\RestoreAdvertisementAction;
use Modules\Advertisements\Actions\ResumeAdvertisementAction;
use Modules\Advertisements\Actions\SubmitAdvertisementAction;
use Modules\Advertisements\Actions\UpdateAdvertisementAction;
use Modules\Advertisements\Events\AdvertisementApproved;
use Modules\Advertisements\Events\AdvertisementArchived;
use Modules\Advertisements\Events\AdvertisementCreated;
use Modules\Advertisements\Events\AdvertisementDeleted;
use Modules\Advertisements\Events\AdvertisementPaused;
use Modules\Advertisements\Events\AdvertisementPublished;
use Modules\Advertisements\Events\AdvertisementRejected;
use Modules\Advertisements\Events\AdvertisementResumed;
use Modules\Advertisements\Events\AdvertisementSubmitted;
use Modules\Advertisements\Events\AdvertisementUpdated;
use Modules\Advertisements\Listeners\ClearCacheListener;
use Modules\Advertisements\Listeners\CreateActivityLogListener;
use Modules\Advertisements\Listeners\RefreshSearchIndexListener;
use Modules\Advertisements\Listeners\SendNotificationListener;
use Modules\Advertisements\Listeners\SendSmsListener;
use Modules\Advertisements\Repositories\Eloquent\AdvertisementDocumentRepository;
use Modules\Advertisements\Repositories\Eloquent\AdvertisementImageRepository;
use Modules\Advertisements\Repositories\Eloquent\AdvertisementLogRepository;
use Modules\Advertisements\Repositories\Eloquent\AdvertisementRepository;
use Modules\Advertisements\Repositories\Eloquent\LoanOfferRepository;
use Modules\Advertisements\Repositories\Interfaces\AdvertisementDocumentRepositoryInterface;
use Modules\Advertisements\Repositories\Interfaces\AdvertisementImageRepositoryInterface;
use Modules\Advertisements\Repositories\Interfaces\AdvertisementLogRepositoryInterface;
use Modules\Advertisements\Repositories\Interfaces\AdvertisementRepositoryInterface;
use Modules\Advertisements\Repositories\Interfaces\LoanOfferRepositoryInterface;
use Modules\Advertisements\Adapters\AdvertisementWorkflowAdapter;
use Modules\Advertisements\Services\AdvertisementLogService;
use Modules\Advertisements\Services\AdvertisementService;
use Modules\Advertisements\Services\AdvertisementValidationService;
use Modules\Advertisements\Services\AdvertisementWorkflowService;
use Modules\Advertisements\Services\LoanOfferService;
use Modules\Advertisements\Repositories\Interfaces\AdvertisementFavoriteRepositoryInterface;
use Modules\Advertisements\Repositories\Eloquent\AdvertisementFavoriteRepository;
use Modules\Advertisements\Services\FavoriteService;
use Modules\Advertisements\Services\AdvertisementRecommendationService;
use Modules\Shared\Services\LocationService;

class AdvertisementsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // merge module config
        $this->mergeConfigFrom(__DIR__ . '/../Config/recommendation.php', 'advertisements.recommendation');
        $this->mergeConfigFrom(__DIR__ . '/../Config/advertisements.php', 'advertisements');

        $this->app->bind(AdvertisementRepositoryInterface::class, AdvertisementRepository::class);
        $this->app->bind(LoanOfferRepositoryInterface::class, LoanOfferRepository::class);
        $this->app->bind(AdvertisementImageRepositoryInterface::class, AdvertisementImageRepository::class);
        $this->app->bind(AdvertisementDocumentRepositoryInterface::class, AdvertisementDocumentRepository::class);
        $this->app->bind(AdvertisementLogRepositoryInterface::class, AdvertisementLogRepository::class);

        $this->app->singleton(LocationService::class, fn () => new LocationService());
        $this->app->singleton(AdvertisementValidationService::class, fn () => new AdvertisementValidationService());
        $this->app->singleton(AdvertisementLogService::class, fn ($app) => new AdvertisementLogService(
            $app->make(AdvertisementLogRepositoryInterface::class),
        ));
        $this->app->singleton(LoanOfferService::class, fn ($app) => new LoanOfferService(
            $app->make(LoanOfferRepositoryInterface::class),
        ));
        $this->app->singleton(AdvertisementService::class, fn ($app) => new AdvertisementService(
            $app->make(AdvertisementRepositoryInterface::class),
            $app->make(LoanOfferRepositoryInterface::class),
            $app->make(AdvertisementImageRepositoryInterface::class),
            $app->make(AdvertisementDocumentRepositoryInterface::class),
            $app->make(AdvertisementLogService::class),
            $app->make(AdvertisementValidationService::class),
            $app->make(AdvertisementWorkflowService::class),
        ));

        // Favorites
        $this->app->bind(AdvertisementFavoriteRepositoryInterface::class, AdvertisementFavoriteRepository::class);
        $this->app->singleton(FavoriteService::class, fn ($app) => new FavoriteService(
            $app->make(AdvertisementFavoriteRepositoryInterface::class)
        ));

        // Recommendation service (simple weighted matching, replaceable by AI later)
        $this->app->singleton(AdvertisementRecommendationService::class, fn ($app) => new AdvertisementRecommendationService(
            $app->make(AdvertisementRepositoryInterface::class)
        ));

        // Bind adapters: SMS (Twilio if configured, otherwise log) and Notification dispatcher
        if (env('TWILIO_SID') && env('TWILIO_AUTH_TOKEN')) {
            $this->app->bind(\Modules\Advertisements\Services\Adapters\SmsAdapterInterface::class, \Modules\Advertisements\Services\Adapters\SmsTwilioAdapter::class);
        } else {
            $this->app->bind(\Modules\Advertisements\Services\Adapters\SmsAdapterInterface::class, \Modules\Advertisements\Services\Adapters\SmsLogAdapter::class);
        }

        $this->app->bind(\Modules\Advertisements\Services\Adapters\NotificationAdapterInterface::class, \Modules\Advertisements\Services\Adapters\NotificationDispatcher::class);

        $this->app->singleton(CreateAdvertisementAction::class, fn ($app) => new CreateAdvertisementAction($app->make(AdvertisementService::class)));
        $this->app->singleton(UpdateAdvertisementAction::class, fn ($app) => new UpdateAdvertisementAction($app->make(AdvertisementService::class)));
        $this->app->singleton(SubmitAdvertisementAction::class, fn ($app) => new SubmitAdvertisementAction($app->make(AdvertisementWorkflowService::class)));
        $this->app->singleton(PauseAdvertisementAction::class, fn ($app) => new PauseAdvertisementAction($app->make(AdvertisementService::class)));
        $this->app->singleton(ResumeAdvertisementAction::class, fn ($app) => new ResumeAdvertisementAction($app->make(AdvertisementService::class)));
        $this->app->singleton(ArchiveAdvertisementAction::class, fn ($app) => new ArchiveAdvertisementAction($app->make(AdvertisementService::class)));
        $this->app->singleton(DeleteAdvertisementAction::class, fn ($app) => new DeleteAdvertisementAction($app->make(AdvertisementService::class)));
    }

    public function registerMiddlewareAliases(): void
    {
        $router = $this->app->make('router');
        $router->aliasMiddleware('verified_mobile', \Modules\Advertisements\Http\Middleware\VerifiedMobile::class);
        $router->aliasMiddleware('approved_kyc', \Modules\Advertisements\Http\Middleware\ApprovedKyc::class);
        $router->aliasMiddleware('not_suspended', \Modules\Advertisements\Http\Middleware\NotSuspended::class);
        $router->aliasMiddleware('advertisement_limit', \Modules\Advertisements\Http\Middleware\AdvertisementLimit::class);
        $router->aliasMiddleware('record_ad_view', \Modules\Advertisements\Http\Middleware\RecordAdvertisementView::class);
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../Routes/api.php');
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
        $this->registerMiddlewareAliases();

        Event::listen(AdvertisementCreated::class, CreateActivityLogListener::class);
        Event::listen(AdvertisementUpdated::class, CreateActivityLogListener::class);
        Event::listen(AdvertisementSubmitted::class, [CreateActivityLogListener::class, 'handle']);
        Event::listen(AdvertisementApproved::class, [CreateActivityLogListener::class, 'handle']);
        Event::listen(AdvertisementRejected::class, [CreateActivityLogListener::class, 'handle']);
        Event::listen(AdvertisementPublished::class, [CreateActivityLogListener::class, 'handle']);
        Event::listen(AdvertisementPaused::class, [CreateActivityLogListener::class, 'handle']);
        Event::listen(AdvertisementResumed::class, [CreateActivityLogListener::class, 'handle']);
        Event::listen(AdvertisementArchived::class, [CreateActivityLogListener::class, 'handle']);
        Event::listen(AdvertisementDeleted::class, [CreateActivityLogListener::class, 'handle']);

        Event::listen(AdvertisementCreated::class, SendNotificationListener::class);
        Event::listen(AdvertisementSubmitted::class, SendSmsListener::class);
        Event::listen(AdvertisementPublished::class, RefreshSearchIndexListener::class);
        Event::listen(AdvertisementUpdated::class, ClearCacheListener::class);

        // Recommendation cache invalidation
        Event::listen(AdvertisementUpdated::class, \Modules\Advertisements\Listeners\BumpRecommendationVersionListener::class);
        Event::listen(AdvertisementPublished::class, \Modules\Advertisements\Listeners\BumpRecommendationVersionListener::class);

        // User-level invalidation on view/favorite
        Event::listen(\Modules\Advertisements\Events\AdvertisementFavorited::class, \Modules\Advertisements\Listeners\InvalidateUserRecommendationListener::class);
        Event::listen(\Modules\Advertisements\Events\AdvertisementViewed::class, \Modules\Advertisements\Listeners\InvalidateUserRecommendationListener::class);
    }
}
