<?php

namespace App\Models;

use App\Traits\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Translatable\HasTranslations;

class Currency extends Model
{
    use HasFactory, LogsActivity, Filterable, HasTranslations;

    public $translatable = ['name', 'symbol'];

    protected $fillable = [
        'name',
        'iso_3',
        'currency_conversion_rate',
        'is_common',
        'symbol',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'name',
                'iso_3',
                'currency_conversion_rate',
                'is_common',
                'symbol',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    protected $casts = ['is_common' => 'boolean'];

    public function scopeCommon()
    {
        return $this->where('is_common', true);
    }
}
