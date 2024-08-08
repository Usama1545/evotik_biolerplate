<?php

namespace App\Integrations\Shops;

use Exception;

/**
 * Class GoogleMerchant
 * Handles interactions with the Google Merchant Center via the Content API for Shopping.
 */
class GoogleMerchant
{
    /**
     * @var \Google\Client The Google Client instance.
     */
    private $client;

    /**
     * @var \Google\Service\ShoppingContent The Google Shopping Content Service instance.
     */
    private $service;

    /**
     * @var string The Merchant ID.
     */
    private $merchantId;

    /**
     * GoogleMerchant constructor.
     * Initializes the Google Client and Merchant Service.
     *
     * @param string $clientId Your Google Cloud Project's Client ID.
     * @param string $clientSecret Your Google Cloud Project's Client Secret.
     * @param string $redirectUri The OAuth2 redirect URI.
     * @param string $merchantId Your Google Merchant Center ID.
     */
    public function __construct()
    {
        $this->merchantId = config('services.google-merchant.merchant_id');

        $this->client = new \Google\Client();
        $this->client->setAuthConfig(__DIR__ . '/google-merchant-auth.json');
        $this->client->setScopes('https://www.googleapis.com/auth/content');
        $this->client->setAccessType('offline');
        
        $this->service = new \Google\Service\ShoppingContent($this->client);
    }

    /**
     * Submits a product to the Google Merchant Center.
     *
     * @param array $productData An associative array containing the product data.
     * @return \Google\Service\ShoppingContent\Product|null The product submission response or null in case of failure.
     * @throws Exception Throws exception if the API call fails.
     */
    public function submitProduct($productData)
    {
        $product = new \Google\Service\ShoppingContent\Product();

        foreach ($productData as $key => $value) {
            $methodName = 'set' . ucfirst($key);
            if (method_exists($product, $methodName)) {
                $product->$methodName($value);
            }
        }

        try {
            $response = $this->service->products->insert($this->merchantId, $product);
            return $response;
        } catch (Exception $e) {
            dd($e->getMessage());

            echo 'Caught exception: ',  $e->getMessage(), "\n";
            return null;
        }
    }
}
