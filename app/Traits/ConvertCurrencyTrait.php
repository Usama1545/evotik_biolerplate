<?php

namespace App\Traits;

use App\Models\Currency;

trait ConvertCurrencyTrait
{
    static $converted = [];

    static $memo_id;

    public function getMemoIdentifierAttribute()
    {
        if (!self::$memo_id)
            self::$memo_id = uniqid("memo-" . uniqid());

        return self::$memo_id;
    }

    public static function bootConvertCurrencyTrait(): void
    {
        static::retrieved(function ($model) {
            $rate = $model->getCurrencyRate($model);
            if ($rate > 0) {
                foreach ($model->conversions as $attribute) {
                    $model->{$attribute} = $model->convertCurrency($model->{$attribute}, $rate);
                    $model->{$attribute . "_formatted"} = getFormattedValue($model->{$attribute});
                }
            }
        });

        static::saving(function ($model) {

            [$converted] = addModelToConvertedModels(get_class($model), $model->memo_identifier);

            $rate = $model->getCurrencyRate($model);
            if ($rate > 0) {
                foreach ($model->conversions ?? [] as $attribute) {
                    $already_converted = get_class($model) . " - $model->id - $attribute";

                    if ($model->isDirty($attribute) && !in_array($already_converted, self::$converted)) {
                        $model->{$attribute} = $model->convertCurrencyToUSD($model->{$attribute}, $rate);
                        self::$converted[] = $already_converted;
                    }

                    if (!in_array($attribute, $model->getFillable())) {
                        unset($model->{$attribute});
                    }

                    unset($model->{$attribute . "_formatted"});
                }
            }
            //clear already_converted attrs once finishing current model
            self::$converted = [];
        });

        static::saved(fn ($model) => $model->refresh());
    }

    /**
     * @param $requested_currency
     * @return null
     */
    public function getRate($requested_currency)
    {
        $rate = null;
        $currency = use_memo($requested_currency, fn () => Currency::where('iso_3', strtoupper($requested_currency))->first());

        if (!empty($currency)) {
            $rate = $currency->currency_conversion_rate;
        } else {
            $currency = use_memo($requested_currency, fn () => Currency::where('iso_3', 'USD')->first(), true);
            $rate = $currency->currency_conversion_rate;
        }

        return $rate;
    }

    /**
     * Rounding only while retrieving data
     * @param $value
     * @param $rate
     * @return float|int
     */
    public function convertCurrency($value, $rate): float|int
    {
        return round($value * $rate, 2);
    }

    /**
     * DO NOT ROUND while Storing
     * @param $value
     * @param $rate
     * @return float|int
     */
    public function convertCurrencyToUSD($value, $rate): float|int
    {
        return $value / $rate;
    }

    /**
     * @param $model
     * @return mixed
     */
    function getCurrencyRate($model): mixed
    {
        $toCurrency = request()->header('Currency', getVisitorCurrency() ?? 'USD');
        return $model->getRate($toCurrency);
    }
}
