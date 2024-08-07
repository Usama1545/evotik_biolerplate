<?php

namespace App\Services;

use App\Jobs\NotificationEmailSendingJob;
use App\Models\Gig;
use App\Models\NotificationChannel;
use App\Models\NotificationTopic;
use App\Models\Order;
use App\Models\User;
use App\Models\UserNotificationSetting;

class NotificationService
{
    public $channelId;
    public function __construct(public $model, public $channelName = null)
    {
    }

    public function publishNotificationToInApp()
    {
    }

    public function handleNotification()
    {
        $notificationChannel = $this->channelName
            ? NotificationChannel::where('is_active', true)->where('name', $this->channelName)->get()
            : NotificationChannel::where('is_active', true)->get();

        foreach ($notificationChannel as $channel) {
            $this->channelId = $channel->id;
            switch ($channel->name) {
                case 'email':
                    $this->publishNotificationToEmail();
                    break;
                case 'pusher':
                    $this->publishNotificationToInApp();
                    break;
            }
        }
    }
    public function publishNotificationToEmail()
    {
        $model = $this->model;
        $attribute = $this->model::NOTIFIABLE_ATTRIBUTE;
        $topics = NotificationTopic::where('is_active', true)->where('model', get_class($model))->where('action', $model->{$attribute})->get();
        foreach ($topics as $topic) {
            switch ($topic->target_user_role) {
                case 'user':
                    //send him email
                    if (get_class($this->model) === Order::class && $topic->target_user_feature === 'seller') {
                        $this->sendOrderEmailToSeller($topic);
                    } else {
                        $this->sendEmailToUser($topic);
                    }
                    break;
                case 'admin':
                    //send to all admins
                    $this->sendEmailToAdmins($topic);
                    break;
            }
        }
    }

    public function sendEmailToUser($topic)
    {
        $user = $this->getUserFromModel();
        $send = $this->checkUserNotificationSettings($user->id);
        if (!$send)
            return;

        $mail_content = $this->getEmailContent($topic);
        $this->dispatchNotificationEmailSendingJob($topic, $user, $mail_content);
    }

    public function sendEmailToAdmins($topic)
    {
        $users = User::role('admin')->get();
        foreach ($users as $user) {
            $send = $this->checkUserNotificationSettings($user->id);
            if (!$send)
                continue;

            $mail_content = $this->getEmailContentForAdmin($topic, $user);
            $this->dispatchNotificationEmailSendingJob($topic, $user, $mail_content);
        }
    }

    public function sendOrderEmailToSeller($topic)
    {
        $user = $this->model?->seller;
        $send = $this->checkUserNotificationSettings($user->id);

        if (!$send)
            return;

        $mail_content = $this->getEmailContentForSeller($topic);
        $this->dispatchNotificationEmailSendingJob($topic, $user, $mail_content);
    }


    public function checkUserNotificationSettings($user_id)
    {
        return UserNotificationSetting::where('user_id', $user_id)->where('notification_channel_id', $this->channelId)->where('is_active', true)->exists();
    }

    public function getUserFromModel()
    {
        $model = $this->model;

        if ($model instanceof User) {
            return $model;
        } else {
            return $model->user;
        }
    }

    protected function getEmailContent($topic)
    {
        $vars = array(
            '*|User Name|*' => $this->model->user->first_name . " " . $this->model->user->last_name,
            '*|Notification Topic|*' => $topic->topic,
            '*|Gig Title|*' => $this->model->title,
            '*|Description|*' => $this->model->description,
            '*|CLIENT_NAME|*' => $this->model->name,
            '*|UID|*' => $this->model->uid,
        );
        return $this->generateTemplateFillables($topic, $vars);
    }

    protected function getEmailContentForAdmin($topic, $user)
    {
        $vars = array(
            '*|User Name|*' => $user->first_name . " " . $user->last_name,
            '*|Notification Topic|*' => $topic->topic,
            '*|Gig Title|*' => $this->model->title,
            '*|Description|*' => $this->model->description,
            '*|CLIENT_NAME|*' => $this->model->name,
            '*|UID|*' => $this->model->uid,
        );
        return $this->generateTemplateFillables($topic, $vars);
    }

    protected function getEmailContentForSeller($topic)
    {
        $vars = array(
            '*|User Name|*' => $this->model?->seller->first_name . " " . $this->model?->seller->last_name,
            '*|Notification Topic|*' => $topic->topic,
            '*|Gig Title|*' => $this->model->title,
            '*|Description|*' => $this->model->description,
            '*|CLIENT_NAME|*' => $this->model->name,
            '*|UID|*' => $this->model->uid,
        );
        return $this->generateTemplateFillables($topic, $vars);
    }

    private function generateTemplateFillables(NotificationTopic $topic, $vars, $text = null)
    {
        $text = strtoupper($text ?? 'review');
        if ($topic->path) {
            if ($topic->model == Gig::class) {
                $vars['*|Action Button Link|*'] = $topic->path . "/" . $this->model->id . "/edit";
            } else if ($topic->model == Order::class) {
                $vars['*|Action Button Link|*'] = $topic->path . "/" . $this->model->id . "/activity";
            } else {
                $vars['*|Action Button Link|*'] = $topic->path . "/" . $this->model->id;
            }

            $vars['*|Action Button|*'] = <<<HTML
            <div style="text-align:center;">
                <a href="{$vars['*|Action Button Link|*']}" target="_blank" style="display: inline-block; padding: 10px 20px; background-color: #4CAF50; color: white; text-align: center; text-decoration: none; border: none; border-radius: 5px; cursor: pointer; transition: background-color 0.3s ease;" onmouseover="this.style.backgroundColor='#45a049'" onmouseout="this.style.backgroundColor='#4CAF50'">$text</a>
            </div>
            HTML;
        } else {
            $vars['*|Action Button|*'] = '';
        }
        return strtr($topic->template, $vars);
    }

    private function dispatchNotificationEmailSendingJob($topic, User $user, $mail_content)
    {
        dispatch(new NotificationEmailSendingJob($this->model, $topic, $user, $mail_content));
    }
}
