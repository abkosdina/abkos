<?php

namespace Modules\Shared\Base;

use Illuminate\Database\Eloquent\Model;

abstract class BaseObserver
{
    public function creating(Model $model): void
    {
    }

    public function updating(Model $model): void
    {
    }
}
