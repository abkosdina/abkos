<?php

namespace Modules\Advertisements\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Modules\Advertisements\Events\AdvertisementSubmitted;
use Modules\Notifications\Notifications\SmsNotification as GlobalSmsNotification;
use Modules\Advertisements\Notifications\SmsNotification as LocalSmsNotification;
use Illuminate\Support\Facades\Notification;
use Modules\Advertisements\Services\Adapters\SmsAdapterInterface;

class SendSmsListener implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(AdvertisementSubmitted $event): void
    {
        $ad = $event->advertisement;
        try {
            $notifiable = $ad->user ?? $ad->owner ?? null;

            if ($notifiable) {
                $adapter = app(SmsAdapterInterface::class);
                $adapter->send($notifiable, "Your advertisement has been submitted", ['advertisement_id' => $ad->id]);
                return;
            }

            Log::info('Advertisement SMS fallback log', ['advertisement_id' => $ad->id]);
        } catch (\Throwable $e) {
            Log::error('SendSmsListener failed: ' . $e->getMessage());
        }
    }
}
