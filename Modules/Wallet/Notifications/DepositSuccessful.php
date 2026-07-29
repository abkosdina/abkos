<?php

namespace Modules\Wallet\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class DepositSuccessful extends Notification
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
            ->subject('Deposit Successful')
            ->line('Your wallet deposit was successful.')
            ->line('Amount: ' . $this->transaction->amount)
            ->line('Transaction ID: ' . $this->transaction->uuid);
    }
}
