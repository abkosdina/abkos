<?php

namespace Modules\Advertisements\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendAdvertisementNotifications implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle($event): void
    {
        // Basic placeholder: produce logs for notification delivery hooks
        $adId = $event->advertisement->uuid ?? null;
        Log::info('SendAdvertisementNotifications invoked', ['event' => get_class($event), 'ad' => $adId]);
        // Here you'd resolve Notification classes and send to relevant users/roles
    }
}
