<?php

namespace Modules\Advertisements\Services\Adapters;

interface NotificationAdapterInterface
{
    /**
     * Dispatch a notification (email/push) to a notifiable entity.
     */
    public function notify(mixed $notifiable, string $title, array $payload = []): void;
}
