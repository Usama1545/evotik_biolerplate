<?php

namespace App\Models;

use App\Traits\Filterable;
use App\Traits\HasUid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory, Filterable, HasUid;

    protected $fillable = [
        'user_id',
        'uid',
        'title',
        'description',
        'status',
        'department',
        'priority',
        'opening_date',
        'closing_date',
        'closed_by',
        'category',
    ];

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function closedBy(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by', 'id');
    }
}
