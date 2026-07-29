<?php

namespace Modules\Advertisements\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class AdvertisementLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'advertisement_id',
        'user_id',
        'action',
        'old_values',
        'new_values',
        'ip',
        'device',
        'created_at',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    public function advertisement(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Advertisement::class);
    }

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
