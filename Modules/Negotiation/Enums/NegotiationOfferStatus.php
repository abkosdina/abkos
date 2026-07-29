<?php

namespace Modules\Negotiation\Enums;

enum NegotiationOfferStatus: string
{
    case Pending = 'Pending';
    case CounterOffer = 'CounterOffer';
    case Accepted = 'Accepted';
    case Rejected = 'Rejected';
    case Expired = 'Expired';
    case Cancelled = 'Cancelled';
}
