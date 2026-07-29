<?php

namespace Modules\Negotiation\Models;

use Illuminate\Database\Eloquent\Model;

class NegotiationHistory extends Model
{
    protected $table = 'negotiation_histories';

    protected $fillable = [
        'negotiation_id',
        'actor_id',
        'event',
        'details',
        'metadata',
    ];

    protected $casts = [
        'details' => 'array',
        'metadata' => 'array',
    ];

    public function negotiation(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Negotiation::class, 'negotiation_id');
    }
}
