<?php

namespace App\Traits;

use Illuminate\Support\Str;

trait Sluggable
{
    protected static function bootSluggable()
    {
        static::saving(function ($model) {
            $sourceAttribute = $model->getSlugSourceAttribute();
            try {
                //code...
                $value = $model->getTranslations($sourceAttribute);
                $value = $value['en'];
            } catch (\Throwable $th) {
                $value = $model->getAttribute($sourceAttribute);
            }
            $model->slug = Str::slug($value);
        });
    }
}
