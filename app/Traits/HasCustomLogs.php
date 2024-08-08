<?php

namespace App\Traits;

use Spatie\Activitylog\Models\Activity;

trait HasCustomLogs
{
    protected static $logOnlyDirty = true;

    public function logs()
    {
        return $this->hasMany(Activity::class, 'subject_id', 'id')
            ->where('subject_type', get_class($this))
            ->leftJoin('users', 'users.id', '=', 'activity_log.causer_id')
            ->select('users.name As user_name', 'activity_log.*');
    }
}
