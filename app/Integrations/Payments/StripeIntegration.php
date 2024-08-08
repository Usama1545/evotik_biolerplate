<?php

namespace App\Integrations\Payments;

use Illuminate\Support\Arr;
use Stripe\Stripe;

class StripeIntegration
{
    protected Stripe $stripe;

    protected array $stripePayload;

    public function __construct(
        protected string $secret,
        protected ?string $success_url = null,
        protected ?string $cancel_url = null,
        protected ?string $currency = 'USD'
    ) {
        if (is_null($this->currency)) {
            $this->currency = 'USD';
        }

        \Stripe\Stripe::setApiKey($this->secret);
    }

    public function setItems($collection, ?string $customer_email = null, ?string $stripe_coupon_id = null)
    {
        $this->stripePayload = [
            'line_items' => [
                ...$collection
            ],
            'mode' => 'payment',
            'success_url' => $this->success_url,
            'cancel_url' => $this->cancel_url,
            'customer_email' => $customer_email
        ];

        if (!is_null($stripe_coupon_id)) {
            $this->setCouponsForStripePayload($stripe_coupon_id);
        }

        return $this;
    }

    public function setShippingFee(float $shipping_fee): self
    {
        $this->stripePayload['line_items'][] = [
            'price_data' => [
                'currency' => $this->currency,
                'product_data' => [
                    'name' => 'Shipping fee',
                ],
                'unit_amount' => $shipping_fee * 100,
            ],
            'quantity' => 1,
        ];

        return $this;
    }

    public function setTax(float $tax): self
    {
        $this->stripePayload['line_items'][] = [
            'price_data' => [
                'currency' => $this->currency,
                'product_data' => [
                    'name' => 'Tax',
                ],
                'unit_amount' => round($tax * 100),
            ],
            'quantity' => 1,
        ];

        return $this;
    }

    public function checkoutSession(array $return_args = []): array
    {
        $response = \Stripe\Checkout\Session::create($this->stripePayload)->toArray();

        return array_values(Arr::only($response, $return_args));
    }

    protected function setCouponsForStripePayload(string $coupons)
    {
        $this->stripePayload['discounts'] = [['coupon' => $coupons]];
    }

    public function createCoupon(string $couponName, float $amount, string $duration = 'once', $currency = 'USD')
    {
        return(new \Stripe\StripeClient($this->secret))->coupons->create([
            'name' => $couponName,
            'amount_off' => $amount,
            'duration' => $duration,
            'currency' => $currency,
        ]);
    }
}
