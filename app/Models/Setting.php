<?php

namespace App\Models;

use App\Traits\HasCustomTranslation;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Setting extends Model
{
    use HasFactory, LogsActivity, HasCustomTranslation;

    const TRANSLATED_VALUES = ['site_name', 'meta'];

    const CURRENCY_ATTRIBUTES = ["min_pricing", "max_pricing", "min_order_flat_rate", "flat_rate_fee",];

    public $translatable = ['value'];
    const SOCIAL_MEDIA = [
        'whatsapp',
        'facebook',
        'instagram',
        'tiktok',
        'pinterest',
        'youtube',
        'twitter',
        'linkedin',
    ];

    protected $fillable = [
        'key',
        'value',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'key',
                'value',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public static function booted()
    {
        static::retrieved(function ($model) {
            //this route for editing settings, they must be in USD always
            if (!in_array(request()->path(), ['api/application-settings']) && in_array($model->key, self::CURRENCY_ATTRIBUTES)) {
                $model->value = convertMoneyToHeaderCurrency($model->value, now());
            }
        });
    }

    public function getSocialLinksAttribute(): string
    {
        $html = "";

        foreach (self::SOCIAL_MEDIA as $social_media) {
            if (
                !empty(huna_settings()[$social_media])
            ) {
                $icon = asset("social_icons/$social_media.png");

                $html .= <<<HTML
                    <td>
                        <a href="{huna_settings()[$social_media]}?ts=1689364554" style="text-decoration: none; padding-right: 10px;" target="_blank">
                            <img alt="{$social_media}" src="{$icon}" style="width: 20px; height: 20px;" />
                        </a>
                    </td>
                HTML;
            }
        }

        return $html;
    }

}
