<?php

namespace Modules\Deals\Events;

use Modules\Deals\Models\Deal;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DealCancelled
{
    use Dispatchable, SerializesModels;

    public Deal $deal;
    public array $payload;

    public function __construct(Deal $deal, array $payload = [])
    {
        $this->deal = $deal;
        $this->payload = $payload;
    }
}
