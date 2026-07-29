<?php

namespace Modules\Documents\Repositories\Eloquent;

use Modules\Documents\Models\DocumentAccessLog;
use Modules\Documents\Repositories\Interfaces\DocumentAccessLogRepositoryInterface;

class DocumentAccessLogRepository implements DocumentAccessLogRepositoryInterface
{
    public function create(array $data): object
    {
        return DocumentAccessLog::query()->create($data);
    }
}
