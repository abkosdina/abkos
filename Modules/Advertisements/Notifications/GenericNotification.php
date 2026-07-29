<?php

namespace Modules\Advertisements\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;

class GenericNotification extends Notification
{
    public string $title;
    public array $payload;

    public function __construct(string $title, array $payload = [])
    {
        $this->title = $title;
        $this->payload = $payload;
    }

    public function via($notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toArray($notifiable): array
    {
        return array_merge(['title' => $this->title], $this->payload);
    }

    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }
}
