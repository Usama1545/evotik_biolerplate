<?php

namespace App\Models;

use App\Traits\Filterable;
use App\Traits\HasCustomLogs;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class EmailSubscription extends Model
{
    use HasFactory, Filterable, SoftDeletes, LogsActivity, HasCustomLogs;

    protected $fillable = [
        'optout',
        'opens',
        'clicks',
        'is_subscribed'
    ];

    protected $casts = [
        'is_subscribed' => 'boolean'
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['optout', 'opens', 'clicks'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

}
