<?php

namespace Modules\Negotiation\Enums;

enum NegotiationStatus: string
{
    case Pending = 'Pending';
    case Active = 'Active';
    case WaitingBuyer = 'WaitingBuyer';
    case WaitingSeller = 'WaitingSeller';
    case Accepted = 'Accepted';
    case Rejected = 'Rejected';
    case Cancelled = 'Cancelled';
    case Expired = 'Expired';

    /**
     * @deprecated Legacy compatibility only. The negotiation module does not
     * automatically convert accepted negotiations to orders.
     */
    case ConvertedToOrder = 'ConvertedToOrder';
    case Closed = 'Closed';
}
