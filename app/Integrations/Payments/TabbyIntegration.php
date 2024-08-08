<?php

namespace App\Integrations\Payments;

use App\Helpers\TabbyOrder;
use App\Helpers\TabbyOrderHistory;
use App\Models\Tenant\TenantBaseModels\Country;
use App\Traits\HasTabbyBuyer;
use App\Traits\HasTabbyShippingAddress;
use Illuminate\Support\Facades\Http;

class TabbyIntegration
{
    use HasTabbyBuyer, HasTabbyShippingAddress;

    /**
     * An array of iso_2 of our countries that represents the countries supported by tabby and user country should be one of those
     * in order to be eligible to use this payment method
     *
     * @var string[] SUPPORTED_COUNTRIES_BY_TABBY
     */
    const SUPPORTED_COUNTRIES_BY_TABBY = ['AE', 'SA', 'KW', 'BH', 'QA'];

    /**
     * Tabby base API URL.
     *
     * @var string TABBY_BASE_URL
     */
    const TABBY_BASE_URL = 'https://api.tabby.ai/api';

    /**
     * @var TabbyOrder $order
     */
    protected TabbyOrder $order;

    /**
     * @var TabbyOrderHistory[] $order_history
     */
    protected array $order_history = [];

    /**
     * @var array $items
     */
    protected array $items = [];

    /**
     * @var Country $customerCountry
     */
    protected Country $customerCountry;

    public function __construct(
        protected string $public_key,
        protected string $secret_key,
        protected ?string $merchant_code = null,
        protected ?string $lang = null,
        protected ?string $currency = null,
        protected ?float $amount = null,
        protected ?string $success_url = null,
        protected ?string $failure_url = null,
        protected ?string $cancel_url = null,
        protected $country = null,
    ) {
        $this->customerCountry = $country ?? countryByIp(true);
    }

    /**
     * Set order info for current payment session.
     *
     * @return self
     */
    public function setOrder(TabbyOrder $order): self
    {
        $this->order = $order;

        return $this;
    }

    /**
     * Set order info for current payment session.
     *
     * @param TabbyOrderHistory[] $tabbyOrderHistory
     * @return self
     */
    public function setOrderHistory(array $tabbyOrderHistory): self
    {
        $this->order_history = $tabbyOrderHistory;

        return $this;
    }

    /**
     * Merge all the data and create new checkout session and return the response as json.
     *
     * @return mixed
     */
    public function create()
    {
        if (!$this->customerFromSupportedCountries()) {
            return null;
        }

        $tabbyApiUrl = self::TABBY_BASE_URL . '/v2/checkout';
        $paymentData = $this->getPaymentData();

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->public_key,
        ])->post($tabbyApiUrl, $paymentData);
        if ($response->failed()) {
            throw $response->toException();
        }

        $jsonResponse = $response->json();

        if ($jsonResponse['status'] !== TabbyOrder::CREATED) {
            return null;
        }

        return $jsonResponse;
    }

    /**
     * Check that the user from supported countries by tabby
     *
     * @return bool
     */
    protected function customerFromSupportedCountries(): bool
    {
        return in_array($this->customerCountry->iso_2, self::SUPPORTED_COUNTRIES_BY_TABBY);
    }

    /**
     * Make sure that the user is eligible for tabby payment method.
     *
     * @return bool
     */
    public function eligible_for_tabby()
    {
        if (!$this->customerFromSupportedCountries()) {
            return false;
        }

        try {
            $status = $this->create()['status'];
        }
        catch (\Exception) {
            return false;
        }

        return $status === TabbyOrder::CREATED;
    }

    /**
     * Send capture request to tabby with a given amount.
     *
     * @return mixed
     */
    public function capture(string $id, $amount)
    {
        $tabbyApiUrl = self::TABBY_BASE_URL . "/v2/payments/{$id}/captures";

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->secret_key,
        ])->post($tabbyApiUrl, [
                    'amount' => $amount
                ]);

        return $response->json();
    }

    /**
     * Merge all the info required for generating a checkout session.
     *
     * @return array
     */
    protected function getPaymentData(): array
    {
        return [
            'payment' => [
                'amount' => round($this->amount),
                'currency' => $this->currency,
                'buyer' => $this->buyer,
                'shipping_address' => $this->shipping_address,
                'order' => $this->order->toArray(),
                'buyer_history' => $this->buyer_history,
                'order_history' => $this->order_history
            ],
            'lang' => $this->lang,
            'merchant_code' => $this->merchant_code,
            "merchant_urls" => [
                "success" => 'https://evotik-ecom.netlify.app/en/invoices',
                "cancel" => 'https://evotik-ecom.netlify.app/en/invoices',
                "failure" => 'https://evotik-ecom.netlify.app/en/invoices'
            ],
        ];
    }
}
