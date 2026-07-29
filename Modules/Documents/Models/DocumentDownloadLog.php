<?php

namespace Modules\Documents\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Shared\Base\BaseModel;

class DocumentDownloadLog extends BaseModel
{
    use HasFactory;

    protected $table = 'document_download_logs';

    public function document()
    {
        return $this->belongsTo(Document::class);
    }
}
