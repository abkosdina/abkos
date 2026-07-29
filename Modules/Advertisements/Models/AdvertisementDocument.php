<?php

namespace Modules\Advertisements\Models;

use Illuminate\Database\Eloquent\Model;

class AdvertisementDocument extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'advertisement_id',
        'document_id',
        'document_type',
    ];

    public function advertisement(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Advertisement::class);
    }
}
