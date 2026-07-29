<?php

namespace Modules\Wallet\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class WithdrawalRequested extends Notification
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
            ->subject('Withdrawal Requested')
            ->line('Your withdrawal request has been received.')
            ->line('Amount: ' . $this->transaction->amount)
            ->line('Transaction ID: ' . $this->transaction->uuid);
    }
}
