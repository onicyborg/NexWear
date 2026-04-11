<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;

trait BlameableTrait
{
    public static function bootBlameableTrait(): void
    {
        static::creating(function ($model) {
            $uid = Auth::id();
            if ($uid) {
                // Set created_by if empty
                if ($model->getAttribute('created_by') === null) {
                    $model->setAttribute('created_by', $uid);
                }
                // Also set updated_by on create
                if ($model->getAttribute('updated_by') === null) {
                    $model->setAttribute('updated_by', $uid);
                }
            }
        });

        static::updating(function ($model) {
            $uid = Auth::id();
            if ($uid) {
                $model->setAttribute('updated_by', $uid);
            }
        });
    }
}
