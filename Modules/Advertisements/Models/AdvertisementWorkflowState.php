<?php

namespace Modules\Advertisements\Models;

use Illuminate\Database\Eloquent\Model;

class AdvertisementWorkflowState extends Model
{
    protected $table = 'advertisement_workflow_states';
    protected $guarded = [];
    protected $casts = [
        'meta' => 'array',
    ];
}
