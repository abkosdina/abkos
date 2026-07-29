<?php

namespace Modules\Wallet\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WalletClosed
{
    use Dispatchable, SerializesModels;

    public function __construct(public object $wallet)
    {
    }
}
