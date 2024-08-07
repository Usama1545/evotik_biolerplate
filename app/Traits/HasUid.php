<?php

namespace App\Traits;


use Illuminate\Support\Str;

trait HasUid
{
    protected static function bootHasUid()
    {
        static::creating(function ($model) {
            $prefix = env('UID_PREFIX', 'HN');
            $randomString = Str::upper(Str::random(10));
            $modelClass = class_basename(get_class($model));
            $modelPrefix = $model->modelPrefix ?? strtoupper($modelClass[0]);
            $model->uid = "#{$prefix}-{$modelPrefix}-{$randomString}";
        });

    }
}
