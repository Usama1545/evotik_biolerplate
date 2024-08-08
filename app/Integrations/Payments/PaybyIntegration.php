<?php

namespace App\Integrations\Payments;

use App\Models\Tenant\TenantBaseModels\Country;
use App\Models\Tenant\TenantGoodsModels\Order;
use Illuminate\Support\Facades\Http;

class PaybyIntegration
{
    /**
     * Represents the supported countries by payby
     * 
     * @var string[]
     */
    const SUPPORTED_ISO_2_COUNTRIES = ['AE'];

    /**
     * Represents the default currency used by payby
     * 
     * @var string
     */
    const DEFAULT_CURRENCY = 'AED';

    /**
     * Represents the default pay scene used for the paybay
     * 
     * @var string
     */
    const PAY_SCENE_CODE = 'PAYPAGE';

    /**
     * The bast URL used for the payby
     * 
     * @var string
     */
    const BASE_URL = 'https://uat.test2pay.com/sgs/api';

    /**
     * Represents the customer country
     * 
     * @var Country $customerCountry
     */
    protected Country $customerCountry;

    public function __construct(
        protected ?string $success_url = null
    )
    {
        $this->customerCountry = countryByIp();
    }

    /**
     * Create new order in payby
     * 
     * @throws Illuminate\Http\Client\RequestException
     * @return array
     */
    public function createOrder(Order $order)
    {
        $data = [
            "requestTime" => time(),
            "bizContent" => [
                "merchantOrderNo" => $order->uid,
                "subject" => 'Here my subject', // needs to discuss it
                "totalAmount" => [
                    "currency" => self::DEFAULT_CURRENCY,
                    "amount" => $order->total_price
                ],
                "paySceneCode" => self::PAY_SCENE_CODE,
                "paySceneParams" => [
                    "redirectUrl" => $this->success_url
                ],
            ]
        ];

        $response = Http::post($this->baseURL . '/acquire2/placeOrder', $data);

        if ($response->failed()) {
            throw $response->toException();
        }

        return $response->json();
    }

    /**
     * Make sure that the user is eligible for payby payment method.
     * 
     * @return bool
     */
    public function eligible_for_payby(): bool
    {
        return in_array($this->customerCountry->iso_2, self::SUPPORTED_ISO_2_COUNTRIES);
    }
}
