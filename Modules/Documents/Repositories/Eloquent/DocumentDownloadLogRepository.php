<?php

namespace Modules\Documents\Repositories\Eloquent;

use Modules\Documents\Models\DocumentDownloadLog;
use Modules\Documents\Repositories\Interfaces\DocumentDownloadLogRepositoryInterface;

class DocumentDownloadLogRepository implements DocumentDownloadLogRepositoryInterface
{
    public function create(array $data): object
    {
        return DocumentDownloadLog::query()->create($data);
    }
}
