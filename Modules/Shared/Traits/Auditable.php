<?php

namespace Modules\Shared\Traits;

trait Auditable
{
    public static function bootAuditable(): void
    {
        static::creating(function ($model): void {
            $model->created_by = $model->created_by ?? auth()->id();
        });

        static::updating(function ($model): void {
            $model->updated_by = $model->updated_by ?? auth()->id();
        });

        static::deleting(function ($model): void {
            $model->deleted_by = $model->deleted_by ?? auth()->id();
        });
    }
}
