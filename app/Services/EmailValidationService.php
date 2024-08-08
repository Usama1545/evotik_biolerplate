<?php

namespace App\Services;

use App\Models\EmailSubscription;
use App\Models\EmailSuppression;

class EmailValidationService
{
    public function verify($email): array
    {
        $overall = true;
        $host = explode("@", $email)[1] ?? 'nonexistantdomainanyway.com';
        $usualHosts = ['gmail.com', 'hotmail.com', 'mail.com', 'yahoo.com', 'outlook.com', 'hotmail.fr', 'yahoo.fr', 'live.com'];
        $response = [];

        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            if (!in_array($host, $usualHosts, true)) {
                $blockedHosts = file(public_path('emails.txt'), FILE_IGNORE_NEW_LINES);

                if (!in_array($host, $blockedHosts)) {
                    if (checkdnsrr($host, 'MX')) {
                        $response['mx_stat'] = true;
                    } else {
                        $response['mx_stat'] = false;
                        $overall = false;
                    }
                } else {
                    $response['blocked_stat'] = false;
                    $overall = false;
                    $response['blocked'] = 'Domain blocked';
                }
            }
        } else {
            $response['filter_stat'] = false;
            $overall = false;
            $response['filter'] = 'Domain blocked';
        }

        $response['overall'] = $overall;

        return $response;
    }

    /**
     * @param $email
     * @return bool
     */
    public function suppression_check($email): bool
    {
        $response = false;
        $is_valid_email = $this->verify($email);
        if (!empty($is_valid_email) && $is_valid_email['overall']) {
            $suppression = EmailSuppression::where('email', $email)->first();
            if (empty($suppression)) {
                $response = true;
            }
        }
        return $response;
    }

    /**
     * @param $model
     * @param $id
     * @return bool
     */
    public function subscription_check($model, $id): bool
    {
        $response = false;
        $subscription = EmailSubscription::where('subscribable_id', $id)
            ->where('subscribable_type', $model)
            ->first();
        if (!empty($subscription)) {
            if ($subscription->is_subscribed) {
                $response = true;
            }
        }
        return $response;
    }

    /**
     * @param $email
     * @param $model
     * @param $id
     * @return bool
     */
    public function send_mail($email, $model, $id): bool
    {
        $response = false;
        $is_valid_email = $this->verify($email);
        if (!empty($is_valid_email) && $is_valid_email['overall']) {
            $suppression = $this->suppression_check($email);
            $subscription = $this->subscription_check($model, $id);
            if ($subscription && $suppression) {
                $response = true;
            }
        }
        return $response;
    }
}
