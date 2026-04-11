<?php

namespace App\Traits;

use App\Support\SystemLogger;

trait SystemLoggable
{
    public static function bootSystemLoggable(): void
    {
        static::created(function ($model) {
            try {
                SystemLogger::record('created', $model->getTable(), (string) $model->getKey(), null, $model->toArray());
            } catch (\Throwable $e) {
                // swallow to avoid breaking main flow
            }
        });

        static::updating(function ($model) {
            try {
                $before = $model->getOriginal();
                $dirty = $model->getDirty();
                $new = array_merge($before, $dirty);
                SystemLogger::record('updated', $model->getTable(), (string) $model->getKey(), $before, $new);
            } catch (\Throwable $e) {
                // swallow
            }
        });

        static::deleting(function ($model) {
            try {
                $before = $model->toArray();
                SystemLogger::record('deleted', $model->getTable(), (string) $model->getKey(), $before, null);
            } catch (\Throwable $e) {
                // swallow
            }
        });
    }
}
