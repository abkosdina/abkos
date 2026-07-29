<?php

namespace Modules\Authentication\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Modules\Authentication\Repositories\Eloquent\OtpRepository;
use Modules\Authentication\Repositories\Eloquent\SessionRepository;
use Modules\Authentication\Repositories\Eloquent\LoginHistoryRepository;
use Modules\Authentication\Repositories\Interfaces\OtpRepositoryInterface;
use Modules\Authentication\Repositories\Interfaces\SessionRepositoryInterface;
use Modules\Authentication\Repositories\Interfaces\LoginHistoryRepositoryInterface;
use Modules\Authentication\Services\OtpService;
use Modules\Authentication\Services\AuthenticationService;
use Modules\Authentication\Services\SessionService;
use Modules\Authentication\Services\SecurityService;
use Modules\Authentication\Services\SmsService;
use Modules\Authentication\Providers\Sms\MockSmsProvider;
use Modules\Authentication\Providers\Sms\SmsProvider;
use Modules\Authentication\Providers\Sms\SmsServiceInterface;

class AuthenticationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../Config/authentication.php', 'authentication');

        $this->app->bind(OtpRepositoryInterface::class, OtpRepository::class);
        $this->app->bind(SessionRepositoryInterface::class, SessionRepository::class);
        $this->app->bind(LoginHistoryRepositoryInterface::class, LoginHistoryRepository::class);

        $this->app->bind(SmsServiceInterface::class, MockSmsProvider::class);
        $this->app->singleton(SmsService::class, fn ($app) => new SmsService($app->make(SmsServiceInterface::class)));

        $this->app->singleton(OtpService::class, fn ($app) => new OtpService($app->make(OtpRepositoryInterface::class), $app->make(SmsService::class), $app->make(SecurityService::class)));
        $this->app->singleton(AuthenticationService::class, fn ($app) => new AuthenticationService($app->make(OtpRepositoryInterface::class), $app->make(SessionRepositoryInterface::class), $app->make(LoginHistoryRepositoryInterface::class), $app->make(SecurityService::class)));
        $this->app->singleton(SessionService::class, fn ($app) => new SessionService($app->make(SessionRepositoryInterface::class), $app->make(LoginHistoryRepositoryInterface::class)));
        $this->app->singleton(SecurityService::class, fn ($app) => new SecurityService());
    }

    public function boot(): void
    {
        Blade::anonymousComponentPath(__DIR__.'/../Resources/views/components', 'auth');

        $this->loadRoutesFrom(__DIR__.'/../Routes/web.php');
        $this->loadRoutesFrom(__DIR__.'/../Routes/api.php');
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'authentication');
    }
}
