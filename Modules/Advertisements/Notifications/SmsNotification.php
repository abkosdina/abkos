<?php

namespace Modules\Advertisements\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;

class SmsNotification extends Notification
{
    public string $message;
    public array $meta;

    public function __construct(string $message, array $meta = [])
    {
        $this->message = $message;
        $this->meta = $meta;
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return array_merge(['message' => $this->message], $this->meta);
    }

    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }
}
