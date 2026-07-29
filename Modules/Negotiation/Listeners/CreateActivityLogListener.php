<?php

namespace Modules\Negotiation\Listeners;

use Illuminate\Support\Facades\Log;

class CreateActivityLogListener
{
    public function handle(object $event): void
    {
        Log::info('Negotiation event', [
            'event' => class_basename($event),
            'payload' => method_exists($event, 'toArray') ? $event->toArray() : [],
        ]);
    }
}
