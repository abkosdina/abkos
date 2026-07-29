<?php

namespace Modules\Chat\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Chat\Repositories\Eloquent\ChatAttachmentRepository;
use Modules\Chat\Repositories\Eloquent\ChatMessageReadRepository;
use Modules\Chat\Repositories\Eloquent\ChatMessageRepository;
use Modules\Chat\Repositories\Eloquent\ChatParticipantRepository;
use Modules\Chat\Repositories\Eloquent\ChatRoomRepository;
use Modules\Chat\Repositories\Interfaces\ChatAttachmentRepositoryInterface;
use Modules\Chat\Repositories\Interfaces\ChatMessageReadRepositoryInterface;
use Modules\Chat\Repositories\Interfaces\ChatMessageRepositoryInterface;
use Modules\Chat\Repositories\Interfaces\ChatParticipantRepositoryInterface;
use Modules\Chat\Repositories\Interfaces\ChatRoomRepositoryInterface;
use Modules\Chat\Interfaces\ChatServiceInterface;
use Modules\Chat\Services\ChatService;

class ChatServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../Config/chat.php', 'chat');

        $this->app->bind(ChatRoomRepositoryInterface::class, ChatRoomRepository::class);
        $this->app->bind(ChatMessageRepositoryInterface::class, ChatMessageRepository::class);
        $this->app->bind(ChatAttachmentRepositoryInterface::class, ChatAttachmentRepository::class);
        $this->app->bind(ChatParticipantRepositoryInterface::class, ChatParticipantRepository::class);
        $this->app->bind(ChatMessageReadRepositoryInterface::class, ChatMessageReadRepository::class);

        $this->app->singleton(ChatService::class, fn ($app) => new ChatService(
            $app->make(ChatRoomRepositoryInterface::class),
            $app->make(ChatMessageRepositoryInterface::class),
            $app->make(ChatAttachmentRepositoryInterface::class),
            $app->make(ChatParticipantRepositoryInterface::class),
            $app->make(ChatMessageReadRepositoryInterface::class),
        ));

        $this->app->alias(ChatService::class, ChatServiceInterface::class);
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../Routes/api.php');
        if (file_exists(__DIR__ . '/../Routes/web.php')) {
            $this->loadRoutesFrom(__DIR__ . '/../Routes/web.php');
        }
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'chat');
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
    }
}
