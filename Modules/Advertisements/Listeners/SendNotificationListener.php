<?php

namespace Modules\Advertisements\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Modules\Advertisements\Events\AdvertisementCreated;
use Modules\Notifications\Notifications\GenericNotification as GlobalGenericNotification;
use Modules\Advertisements\Notifications\GenericNotification as LocalGenericNotification;
use Modules\Advertisements\Services\Adapters\NotificationAdapterInterface;

class SendNotificationListener implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(AdvertisementCreated $event): void
    {
        $ad = $event->advertisement;

        try {
            $notifiable = $ad->user ?? $ad->owner ?? null;

            if ($notifiable) {
                $adapter = app(NotificationAdapterInterface::class);
                $adapter->notify($notifiable, 'Advertisement created', ['advertisement_id' => $ad->id]);
                return;
            }

            Log::info('Advertisement notification fallback log', ['advertisement_id' => $ad->id]);
        } catch (\Throwable $e) {
            Log::error('SendNotificationListener failed: ' . $e->getMessage());
        }
    }
}
