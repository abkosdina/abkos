<?php

namespace Modules\Negotiation\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Negotiation\Enums\NegotiationOfferStatus;

class NegotiationOffer extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'negotiation_offers';

    protected $fillable = [
        'uuid',
        'negotiation_id',
        'created_by',
        'amount',
        'description',
        'status',
        'expires_at',
        'accepted_at',
        'rejected_at',
    ];

    protected $casts = [
        'status' => NegotiationOfferStatus::class,
        'expires_at' => 'datetime',
        'accepted_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    public function negotiation(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Negotiation::class, 'negotiation_id');
    }
}
