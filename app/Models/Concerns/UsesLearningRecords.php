<?php

/** Hii model huwakilisha na kusimamia data ya sehemu hii ya mfumo. */

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;

trait UsesLearningRecords
{
    protected static function bootUsesLearningRecords(): void
    {
        static::addGlobalScope('record_type', function (Builder $builder): void {
            $builder->where($builder->qualifyColumn('record_type'), static::recordType());
        });

        static::creating(function ($model): void {
            $model->record_type = static::recordType();
        });
    }

    public static function recordType(): string
    {
        return strtolower(class_basename(static::class));
    }
}
