<?php

namespace App\Models;

use App\Traits\Filterable;
use App\Traits\HasUid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IssueReport extends Model
{
    use HasFactory, Filterable, HasUid;

    protected $fillable = [
        'uid',
        'path',
        'status',
        'issue',
        'description',
        'uid',
        'model_type',
        'model_id',
    ];

    public function uploads()
    {
        return $this->morphOne(Upload::class, 'model');
    }

    public function model()
    {
        return $this->morphTo();
    }

    public function getUsernameAttribute()
    {
        return $this->model ? $this->model->username : null;
    }

}
