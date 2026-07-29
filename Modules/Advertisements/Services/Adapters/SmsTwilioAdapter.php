<?php

namespace Modules\Advertisements\Services\Adapters;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class SmsTwilioAdapter implements SmsAdapterInterface
{
    protected Client $http;

    public function __construct()
    {
        $this->http = new Client(['timeout' => 5.0]);
    }

    public function send(mixed $notifiable, string $message, array $meta = []): void
    {
        $to = is_object($notifiable) ? ($notifiable->mobile ?? $notifiable->phone ?? null) : null;
        $sid = env('TWILIO_SID');
        $token = env('TWILIO_AUTH_TOKEN');
        $from = env('TWILIO_FROM');

        if (! $to || ! $sid || ! $token || ! $from) {
            Log::warning('SmsTwilioAdapter missing config or recipient', ['to' => $to]);
            return;
        }

        $url = "https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json";

        try {
            $this->http->post($url, [
                'auth' => [$sid, $token],
                'form_params' => [
                    'From' => $from,
                    'To' => $to,
                    'Body' => $message,
                ],
            ]);
        } catch (\Throwable $e) {
            Log::error('SmsTwilioAdapter send failed: ' . $e->getMessage());
        }
    }
}
