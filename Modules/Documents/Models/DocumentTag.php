<?php

namespace Modules\Documents\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Shared\Base\BaseModel;

class DocumentTag extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $table = 'document_tags';

    public function documents()
    {
        return $this->belongsToMany(Document::class, 'document_tag_assignments');
    }
}
