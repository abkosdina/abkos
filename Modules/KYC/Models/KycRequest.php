<?php

namespace Modules\KYC\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Shared\Base\BaseModel;

class KycRequest extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $table = 'kyc_requests';

    protected $casts = [
        'deadline_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function workflowInstance()
    {
        return $this->belongsTo(\Modules\Workflow\Models\WorkflowInstance::class);
    }

    public function currentStep()
    {
        return $this->belongsTo(\Modules\Workflow\Models\WorkflowStep::class, 'current_step_id');
    }

    public function profile()
    {
        return $this->hasOne(KycProfile::class);
    }

    public function reviewLogs()
    {
        return $this->hasMany(KycReviewLog::class);
    }

    public function rejections()
    {
        return $this->hasMany(KycRejectionReason::class);
    }

    public function statusHistory()
    {
        return $this->hasMany(KycStatusHistory::class);
    }

    public function documents()
    {
        return $this->hasMany(KycDocument::class, 'kyc_request_id');
    }

    public function identitySnapshots()
    {
        return $this->hasMany(KycIdentitySnapshot::class, 'kyc_request_id');
    }
}
