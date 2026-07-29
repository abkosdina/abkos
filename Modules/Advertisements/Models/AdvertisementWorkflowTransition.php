<?php

namespace Modules\Advertisements\Models;

use Illuminate\Database\Eloquent\Model;

class AdvertisementWorkflowTransition extends Model
{
    protected $table = 'advertisement_workflow_transitions';
    protected $guarded = [];
    protected $casts = [
        'rules' => 'array',
        'meta' => 'array',
    ];
}
