<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailMeta extends Model
{
    use HasFactory;
    protected $fillable = [
        'uid',
        'is_open',
        'is_clicked',
        'is_bounced',
        'message_id'
    ];

    public function casts(): array
    {
        return [
            "is_open" => "boolean",
            "is_clicked" => "boolean",
            "is_bounced" => "boolean",
        ];
    }
}
