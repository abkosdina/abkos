<?php

namespace Modules\Documents\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Documents\Repositories\Eloquent\DocumentRepository;
use Modules\Documents\Repositories\Eloquent\DocumentTypeRepository;
use Modules\Documents\Repositories\Eloquent\DocumentVersionRepository;
use Modules\Documents\Repositories\Eloquent\DocumentCategoryRepository;
use Modules\Documents\Repositories\Eloquent\DocumentTagRepository;
use Modules\Documents\Repositories\Eloquent\DocumentMetadataRepository;
use Modules\Documents\Repositories\Eloquent\DocumentLinkRepository;
use Modules\Documents\Repositories\Eloquent\DocumentAccessLogRepository;
use Modules\Documents\Repositories\Eloquent\DocumentDownloadLogRepository;
use Modules\Documents\Repositories\Interfaces\DocumentRepositoryInterface;
use Modules\Documents\Repositories\Interfaces\DocumentTypeRepositoryInterface;
use Modules\Documents\Repositories\Interfaces\DocumentVersionRepositoryInterface;
use Modules\Documents\Repositories\Interfaces\DocumentCategoryRepositoryInterface;
use Modules\Documents\Repositories\Interfaces\DocumentTagRepositoryInterface;
use Modules\Documents\Repositories\Interfaces\DocumentMetadataRepositoryInterface;
use Modules\Documents\Repositories\Interfaces\DocumentLinkRepositoryInterface;
use Modules\Documents\Repositories\Interfaces\DocumentAccessLogRepositoryInterface;
use Modules\Documents\Repositories\Interfaces\DocumentDownloadLogRepositoryInterface;
use Modules\Documents\Services\DocumentService;
use Modules\Documents\Services\StorageService;
use Modules\Documents\Services\VersionService;
use Modules\Documents\Services\DownloadService;
use Modules\Documents\Services\SharingService;
use Modules\Documents\Services\HashService;

class DocumentsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../Config/documents.php', 'documents');

        $this->app->bind(DocumentRepositoryInterface::class, DocumentRepository::class);
        $this->app->bind(DocumentTypeRepositoryInterface::class, DocumentTypeRepository::class);
        $this->app->bind(DocumentVersionRepositoryInterface::class, DocumentVersionRepository::class);
        $this->app->bind(DocumentCategoryRepositoryInterface::class, DocumentCategoryRepository::class);
        $this->app->bind(DocumentTagRepositoryInterface::class, DocumentTagRepository::class);
        $this->app->bind(DocumentMetadataRepositoryInterface::class, DocumentMetadataRepository::class);
        $this->app->bind(DocumentLinkRepositoryInterface::class, DocumentLinkRepository::class);
        $this->app->bind(DocumentAccessLogRepositoryInterface::class, DocumentAccessLogRepository::class);
        $this->app->bind(DocumentDownloadLogRepositoryInterface::class, DocumentDownloadLogRepository::class);

        $this->app->singleton(HashService::class, fn ($app) => new HashService());
        $this->app->singleton(StorageService::class, fn ($app) => new StorageService(config('documents.storage.driver')));
        $this->app->singleton(VersionService::class, fn ($app) => new VersionService(
            $app->make(DocumentVersionRepositoryInterface::class),
            $app->make(DocumentRepositoryInterface::class)
        ));
        $this->app->singleton(DownloadService::class, fn ($app) => new DownloadService(
            $app->make(DocumentRepositoryInterface::class),
            $app->make(DocumentDownloadLogRepositoryInterface::class)
        ));
        $this->app->singleton(SharingService::class, fn ($app) => new SharingService(
            $app->make(DocumentRepositoryInterface::class),
            $app->make(DocumentLinkRepositoryInterface::class)
        ));
        $this->app->singleton(DocumentService::class, fn ($app) => new DocumentService(
            $app->make(DocumentRepositoryInterface::class),
            $app->make(DocumentTypeRepositoryInterface::class),
            $app->make(DocumentCategoryRepositoryInterface::class),
            $app->make(DocumentTagRepositoryInterface::class),
            $app->make(DocumentMetadataRepositoryInterface::class),
            $app->make(DocumentLinkRepositoryInterface::class),
            $app->make(DocumentVersionRepositoryInterface::class),
            $app->make(StorageService::class),
            $app->make(HashService::class),
            $app->make(VersionService::class)
        ));
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../Routes/api.php');
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
    }
}
