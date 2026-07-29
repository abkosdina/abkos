<?php

namespace Modules\Advertisements\Services\Adapters;

use Illuminate\Support\Facades\Log;

class SmsLogAdapter implements SmsAdapterInterface
{
    public function send(mixed $notifiable, string $message, array $meta = []): void
    {
        $recipient = null;
        if (is_object($notifiable)) {
            $recipient = $notifiable->mobile ?? $notifiable->phone ?? $notifiable->email ?? null;
        }

        Log::info('SmsLogAdapter send', ['to' => $recipient, 'message' => $message, 'meta' => $meta]);
    }
}
