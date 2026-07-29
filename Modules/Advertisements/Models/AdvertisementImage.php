<?php

namespace Modules\Advertisements\Models;

use Illuminate\Database\Eloquent\Model;

class AdvertisementImage extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'advertisement_id',
        'media_id',
        'sort_order',
        'is_cover',
    ];

    public function advertisement(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Advertisement::class);
    }
}
