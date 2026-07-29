<?php

namespace Modules\Wallet\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BalanceUnlocked
{
    use Dispatchable, SerializesModels;

    public function __construct(public object $lock)
    {
    }
}
