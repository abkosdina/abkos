<?php

namespace Modules\Documents\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Shared\Base\BaseModel;

class DocumentCategory extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $table = 'document_categories';

    public function documents()
    {
        return $this->hasMany(Document::class, 'category_id');
    }
}
