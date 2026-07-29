<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Authentication\Providers\AuthenticationServiceProvider as ModuleAuthenticationServiceProvider;

class AuthenticationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->register(ModuleAuthenticationServiceProvider::class);
    }
}
