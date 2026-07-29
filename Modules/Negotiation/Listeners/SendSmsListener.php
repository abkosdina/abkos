<?php

namespace Modules\Negotiation\Listeners;

use Illuminate\Support\Facades\Log;

class SendSmsListener
{
    public function handle(object $event): void
    {
        Log::info('Negotiation SMS dispatched', ['event' => class_basename($event)]);
    }
}
