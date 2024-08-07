<?php

namespace App\Models;

use App\Jobs\TranslationJob;
use App\Traits\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Meta extends Model
{
    use HasFactory, Filterable, HasTranslations;
    protected $translatable = ['title_translation', 'description_translation'];

    protected $fillable = [
        'title',
        'description',
        'model_id',
        'model_type',
        "title_translation",
        "description_translation",
    ];

    public $appends = ['image'];

    public static function booted()
    {
        static::saved(function ($model) {
            if ($model->isDirty('title') || $model->isDirty('description')) {
                dispatch(new TranslationJob($model, ['title', 'description']));
            }
        });
    }
    
    public function getImageAttribute()
    {
        return $this->upload?->url;
    }

    public function model()
    {
        return $this->morphTo();
    }

    public function upload()
    {
        return $this->morphOne(Upload::class, 'model');
    }

}
