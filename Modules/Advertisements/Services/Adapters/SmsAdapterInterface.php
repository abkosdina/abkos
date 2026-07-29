<?php

namespace Modules\Advertisements\Services\Adapters;

interface SmsAdapterInterface
{
    /**
     * Send an SMS to the given notifiable (user) with message and meta.
     * @param mixed $notifiable
     */
    public function send(mixed $notifiable, string $message, array $meta = []): void;
}
