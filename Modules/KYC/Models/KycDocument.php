<?php

namespace Modules\KYC\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Shared\Base\BaseModel;

class KycDocument extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $table = 'kyc_documents';

    protected $casts = [
        'uploaded_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'metadata' => 'array',
    ];

    protected $fillable = [
        'uuid',
        'kyc_request_id',
        'document_type',
        'file_path',
        'original_filename',
        'mime_type',
        'file_size',
        'file_hash',
        'document_status',
        'uploaded_by',
        'uploaded_at',
        'reviewed_at',
        'reviewed_by',
        'rejection_reason',
        'metadata',
    ];

    /**
     * Get the KYC request this document belongs to
     */
    public function kycRequest()
    {
        return $this->belongsTo(KycRequest::class, 'kyc_request_id');
    }

    /**
     * Get the user who uploaded this document
     */
    public function uploadedByUser()
    {
        return $this->belongsTo(\App\Models\User::class, 'uploaded_by');
    }

    /**
     * Get the reviewer who reviewed this document
     */
    public function reviewedByUser()
    {
        return $this->belongsTo(\App\Models\User::class, 'reviewed_by');
    }

    /**
     * Check if document is approved
     */
    public function isApproved(): bool
    {
        return $this->document_status === 'approved';
    }

    /**
     * Check if document is rejected
     */
    public function isRejected(): bool
    {
        return $this->document_status === 'rejected';
    }

    /**
     * Check if document is under review
     */
    public function isUnderReview(): bool
    {
        return $this->document_status === 'under_review';
    }

    /**
     * Check if document needs replacement
     */
    public function needsReplacement(): bool
    {
        return $this->document_status === 'needs_replacement';
    }
}
