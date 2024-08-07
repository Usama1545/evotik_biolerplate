<?php

namespace App\Models;

use App\Enums\NotificationTypeEnum;
use App\Traits\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class NotificationTopic extends Model
{
    use HasFactory, Filterable, HasTranslations;

    public $translatable = ['in_app_template',];

    protected $fillable = [
        'topic',
        'type',
        'model',
        'action',
        'is_active',
        'template',
        'in_app_template',
        'path',
        'target_user_role',
        'target_user_feature',
    ];

    public function casts(): array
    {
        return [
            'type' => NotificationTypeEnum::class,
            'is_active' => 'boolean',
            'created_at' => "date:Y-m-d H:i"
        ];
    }
}
