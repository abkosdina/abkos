<?php

namespace Modules\Wallet\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class WithdrawalCompleted extends Notification
{
    use Queueable;

    public function __construct(public object $transaction)
    {
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject('Withdrawal Completed')
            ->line('Your withdrawal has been completed.')
            ->line('Amount: ' . $this->transaction->amount)
            ->line('Transaction ID: ' . $this->transaction->uuid);
    }
}
