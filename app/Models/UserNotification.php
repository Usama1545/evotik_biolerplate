<?php

namespace App\Models;

use App\Enums\UserFeatureEnum;
use App\Traits\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserNotification extends Model
{
    use HasFactory, Filterable;

    protected $fillable = [
        'user_id',
        'notification_topic_id',
        'model_type',
        'model_id',
        'read_at', //upadte on retrieved() if null
        'ack_at', //updated on clicked() if null
        'expire_at'
    ];

    public function model()
    {
        return $this->morphTo();
    }

    public static function booted()
    {
        static::addGlobalScope("auth_notifications", function ($model) {
            return $model->where('user_id', auth('user')->id());
        });
    }

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class)->withDefault([
            'username' => __('messages.deleted_account'),
        ]);
    }

    public function topic()
    {
        return $this->belongsTo(NotificationTopic::class, 'notification_topic_id');
    }

    public function getNotificationAttribute()
    {
        return $this->getInAppContent();
    }

    protected function getInAppContent()
    {
        $topic = $this->topic;

        if (!$topic) {
            return [];
        }

        $vars = array(
            '*|UID|*' => $this->model->uid,
            '*|Gig Title|*' => $this->model->title,
            '*|Description|*' => $this->model->description,
            '*|CLIENT_NAME|*' => $this->model->name,
            '*|Sender|*' => $this->getSenderNameForInAppContent(),
        );
        return generateInAppTemplateFillables($topic, $this->model, $vars);
    }

    private function getSenderNameForInAppContent(): string
    {
        if ($this->model instanceof OrderMessage) {
            return $this->model?->getRecipient()?->username;
        } else {
            return  $this->model?->user?->username;
        }
    }
}
