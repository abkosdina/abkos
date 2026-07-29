<?php

namespace Modules\Advertisements\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdvertisementWorkflowAudit extends Model
{
    protected $table = 'advertisement_workflow_audits';
    protected $guarded = [];
    protected $casts = [
        'extra' => 'array',
    ];

    public function advertisement(): BelongsTo
    {
        return $this->belongsTo(Advertisement::class, 'advertisement_uuid', 'uuid');
    }
}
