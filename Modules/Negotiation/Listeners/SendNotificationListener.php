<?php

namespace Modules\Negotiation\Listeners;

use Illuminate\Support\Facades\Log;

class SendNotificationListener
{
    public function handle(object $event): void
    {
        Log::info('Negotiation notification dispatched', ['event' => class_basename($event)]);
    }
}
