<?php

namespace Modules\Documents\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Shared\Base\BaseModel;

class DocumentVersion extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $table = 'document_versions';

    protected $casts = [
        'size' => 'integer',
    ];

    public function document()
    {
        return $this->belongsTo(Document::class);
    }
}
