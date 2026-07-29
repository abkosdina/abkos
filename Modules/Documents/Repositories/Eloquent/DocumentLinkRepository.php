<?php

namespace Modules\Documents\Repositories\Eloquent;

use Modules\Documents\Models\DocumentLink;
use Modules\Documents\Repositories\Interfaces\DocumentLinkRepositoryInterface;

class DocumentLinkRepository implements DocumentLinkRepositoryInterface
{
    public function create(array $data): object
    {
        return DocumentLink::query()->create($data);
    }

    public function find(int|string $id, array $columns = ['*']): ?object
    {
        return DocumentLink::query()->find($id, $columns);
    }
}
