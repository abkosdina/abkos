<?php

namespace Modules\Documents\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Shared\Base\BaseModel;

class DocumentLink extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $table = 'document_links';

    protected $casts = [
        'is_public' => 'boolean',
        'expires_at' => 'datetime',
    ];

    public function document()
    {
        return $this->belongsTo(Document::class);
    }
}
