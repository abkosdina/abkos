<?php

namespace Modules\Documents\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Shared\Base\BaseModel;

class Document extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $table = 'documents';

    protected $casts = [
        'size' => 'integer',
    ];

    public function type()
    {
        return $this->belongsTo(DocumentType::class, 'document_type_id');
    }

    public function category()
    {
        return $this->belongsTo(DocumentCategory::class, 'category_id');
    }

    public function owner()
    {
        return $this->morphTo();
    }

    public function uploader()
    {
        return $this->belongsTo(\Modules\UserManagement\Models\User::class, 'uploaded_by');
    }

    public function versions()
    {
        return $this->hasMany(DocumentVersion::class);
    }

    public function metadata()
    {
        return $this->hasMany(DocumentMetadata::class);
    }

    public function links()
    {
        return $this->hasMany(DocumentLink::class);
    }

    public function accessLogs()
    {
        return $this->hasMany(DocumentAccessLog::class);
    }

    public function downloadLogs()
    {
        return $this->hasMany(DocumentDownloadLog::class);
    }
}
