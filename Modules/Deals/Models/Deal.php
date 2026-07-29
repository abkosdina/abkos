<?php

namespace Modules\Deals\Models;

use Modules\Shared\Base\BaseModel;
use Modules\Deals\Enums\DealStatus;
use App\Models\WorkflowInstance;
use App\Models\User;
use Modules\Advertisements\Models\Advertisement;
use Modules\Negotiation\Models\Negotiation;

class Deal extends BaseModel
{
    protected $table = 'deals';

    protected $fillable = [
        'uuid',
        'negotiation_id',
        'advertisement_id',
        'buyer_id',
        'seller_id',
        'workflow_instance_id',
        'agreed_price',
        'status',
        'accepted_at',
        'closed_at',
        'metadata',
    ];

    protected $casts = [
        'status' => DealStatus::class,
        'agreed_price' => 'decimal:2',
        'accepted_at' => 'datetime',
        'closed_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function negotiation()
    {
        return $this->belongsTo(Negotiation::class, 'negotiation_id');
    }

    public function advertisement()
    {
        return $this->belongsTo(Advertisement::class, 'advertisement_id');
    }

    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function workflowInstance()
    {
        return $this->belongsTo(WorkflowInstance::class, 'workflow_instance_id');
    }
}
