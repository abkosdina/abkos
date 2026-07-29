<?php

namespace Modules\Advertisements\Services\Adapters;

use Illuminate\Support\Facades\Notification;
use Modules\Advertisements\Notifications\GenericNotification;
use Illuminate\Support\Facades\Log;

class NotificationDispatcher implements NotificationAdapterInterface
{
    public function notify(mixed $notifiable, string $title, array $payload = []): void
    {
        if (! $notifiable) {
            Log::warning('NotificationDispatcher: no notifiable provided', ['title' => $title]);
            return;
        }

        try {
            if (class_exists('\\Modules\\Notifications\\Notifications\\GenericNotification')) {
                $class = '\\Modules\\Notifications\\Notifications\\GenericNotification';
                Notification::send($notifiable, new $class($title, $payload));
                return;
            }

            Notification::send($notifiable, new GenericNotification($title, $payload));
        } catch (\Throwable $e) {
            Log::error('NotificationDispatcher failed: ' . $e->getMessage());
        }
    }
}
