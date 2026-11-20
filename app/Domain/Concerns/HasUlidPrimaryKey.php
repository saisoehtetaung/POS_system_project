<?php

namespace App\Domain\Concerns;

use Illuminate\Support\Str;

trait HasUlidPrimaryKey
{
    public function initializeHasUlidPrimaryKey(): void
    {
        $this->incrementing = false;
        $this->keyType = 'string';
    }

    protected static function bootHasUlidPrimaryKey(): void
    {
        static::creating(function ($model): void {
            if (! $model->getKey()) {
                $model->setAttribute($model->getKeyName(), (string) Str::ulid());
            }
        });
    }
}
