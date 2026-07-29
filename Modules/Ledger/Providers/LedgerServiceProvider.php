<?php

namespace Modules\Ledger\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Ledger\Repositories\Eloquent\AccountRepository;
use Modules\Ledger\Repositories\Eloquent\BalanceRepository;
use Modules\Ledger\Repositories\Eloquent\JournalRepository;
use Modules\Ledger\Repositories\Eloquent\LedgerRepository;
use Modules\Ledger\Repositories\Interfaces\AccountRepositoryInterface;
use Modules\Ledger\Repositories\Interfaces\BalanceRepositoryInterface;
use Modules\Ledger\Repositories\Interfaces\JournalRepositoryInterface;
use Modules\Ledger\Repositories\Interfaces\LedgerRepositoryInterface;
use Modules\Ledger\Services\AccountingService;
use Modules\Ledger\Services\BalanceService;
use Modules\Ledger\Services\JournalService;
use Modules\Ledger\Services\LedgerService;
use Modules\Ledger\Services\TransactionService;

class LedgerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../Config/ledger.php', 'ledger');

        $this->app->bind(AccountRepositoryInterface::class, AccountRepository::class);
        $this->app->bind(LedgerRepositoryInterface::class, LedgerRepository::class);
        $this->app->bind(BalanceRepositoryInterface::class, BalanceRepository::class);
        $this->app->bind(JournalRepositoryInterface::class, JournalRepository::class);

        $this->app->singleton(BalanceService::class, fn ($app) => new BalanceService($app->make(LedgerRepositoryInterface::class), $app->make(BalanceRepositoryInterface::class)));
        $this->app->singleton(JournalService::class, fn ($app) => new JournalService($app->make(JournalRepositoryInterface::class)));
        $this->app->singleton(AccountingService::class, fn ($app) => new AccountingService($app->make(LedgerRepositoryInterface::class), $app->make(JournalService::class)));
        $this->app->singleton(TransactionService::class, fn ($app) => new TransactionService(
            $app->make(AccountingService::class),
            $app->make(BalanceService::class),
            $app->make(AccountRepositoryInterface::class),
            $app->make(LedgerRepositoryInterface::class)
        ));
        $this->app->singleton(LedgerService::class, fn ($app) => new LedgerService($app->make(TransactionService::class), $app->make(AccountRepositoryInterface::class), $app->make(BalanceService::class)));
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../Routes/api.php');
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
    }
}
