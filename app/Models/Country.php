<?php

namespace App\Models;

use App\Traits\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Country extends Model
{
    use HasFactory, LogsActivity, Filterable;

    protected $table = 'countries';

    protected $fillable = [
        'iso_2',
        'iso_3',
        'name',
        'currency_id',
        'calling_code',
        'is_common'
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'iso_2',
                'iso_3',
                'name',
                'currency_id',
                'calling_code',
                'is_common'
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    protected $casts = ['is_common' => 'boolean'];

    public function scopeCommon()
    {
        return $this->where('is_common', true);
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function cities(): HasMany
    {
        return $this->hasMany(City::class);
    }

}
