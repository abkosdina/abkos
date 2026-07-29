<?php

namespace Modules\Wallet\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Wallet\Repositories\Eloquent\WalletRepository;
use Modules\Wallet\Repositories\Eloquent\WalletBalanceRepository;
use Modules\Wallet\Repositories\Eloquent\WalletTransactionRepository;
use Modules\Wallet\Repositories\Eloquent\WalletLockRepository;
use Modules\Wallet\Repositories\Eloquent\WalletLimitRepository;
use Modules\Wallet\Repositories\Eloquent\FinancialTransactionRepository;
use Modules\Wallet\Repositories\Interfaces\WalletRepositoryInterface;
use Modules\Wallet\Repositories\Interfaces\WalletBalanceRepositoryInterface;
use Modules\Wallet\Repositories\Interfaces\WalletTransactionRepositoryInterface;
use Modules\Wallet\Repositories\Interfaces\FinancialTransactionRepositoryInterface;
use Modules\Wallet\Repositories\Interfaces\WalletLockRepositoryInterface;
use Modules\Wallet\Repositories\Interfaces\WalletLimitRepositoryInterface;
use Modules\Wallet\Services\WalletService;
use Modules\Wallet\Services\BalanceService;
use Modules\Wallet\Services\TransferService;
use Modules\Wallet\Services\WalletLimitService;
use Modules\Wallet\Services\WalletLockService;
use Modules\Ledger\Services\LedgerService;
use Modules\Ledger\Services\BalanceService as LedgerBalanceService;

class WalletServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../Config/wallet.php', 'wallet');

        $this->app->bind(WalletRepositoryInterface::class, WalletRepository::class);
        $this->app->bind(WalletBalanceRepositoryInterface::class, WalletBalanceRepository::class);
        $this->app->bind(WalletTransactionRepositoryInterface::class, WalletTransactionRepository::class);
        $this->app->bind(FinancialTransactionRepositoryInterface::class, FinancialTransactionRepository::class);
        $this->app->bind(WalletLockRepositoryInterface::class, WalletLockRepository::class);
        $this->app->bind(WalletLimitRepositoryInterface::class, WalletLimitRepository::class);

        $this->app->singleton(BalanceService::class, fn ($app) => new BalanceService(
            $app->make(WalletRepositoryInterface::class),
            $app->make(WalletBalanceRepositoryInterface::class),
            $app->make(LedgerService::class),
            $app->make(LedgerBalanceService::class),
        ));

        $this->app->singleton(WalletLimitService::class, fn ($app) => new WalletLimitService(
            $app->make(WalletLimitRepositoryInterface::class)
        ));

        $this->app->singleton(WalletLockService::class, fn ($app) => new WalletLockService(
            $app->make(WalletLockRepositoryInterface::class),
            $app->make(WalletBalanceRepositoryInterface::class)
        ));

        $this->app->singleton(TransferService::class, fn ($app) => new TransferService(
            $app->make(WalletRepositoryInterface::class),
            $app->make(LedgerService::class),
            $app->make(BalanceService::class),
            $app->make(WalletTransactionRepositoryInterface::class),
            $app->make(FinancialTransactionRepositoryInterface::class)
        ));

        $this->app->singleton(WalletService::class, fn ($app) => new WalletService(
            $app->make(WalletRepositoryInterface::class),
            $app->make(WalletBalanceRepositoryInterface::class),
            $app->make(WalletTransactionRepositoryInterface::class),
            $app->make(FinancialTransactionRepositoryInterface::class),
            $app->make(WalletLockService::class),
            $app->make(WalletLimitService::class),
            $app->make(LedgerService::class),
            $app->make(BalanceService::class),
            $app->make(TransferService::class)
        ));
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../Routes/api.php');
        // Load admin routes for wallet adjustments
        if (file_exists(__DIR__ . '/../Routes/admin.php')) {
            $this->loadRoutesFrom(__DIR__ . '/../Routes/admin.php');
        }
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
    }
}
