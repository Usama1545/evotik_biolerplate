<?php

namespace App\Models;

use App\Traits\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class City extends Model
{
    use HasFactory, LogsActivity, Filterable;

    protected $table = 'cities';

    protected $fillable = [
        'name',
        'country_id',
        'state_id'
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'name',
                'country_id',
                'state_id'
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function state()
    {
        return $this->belongsTo(State::class);
    }

}
