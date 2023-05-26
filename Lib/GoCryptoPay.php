<?php
namespace Eligmaltd\GoCryptoPay\Lib;

use GuzzleHttp;
use GuzzleHttp\Exception\RequestException;

class GoCryptoPay
{
	/* @var GoCryptoLogger $logger*/
    public $logger;
	/* @var GoCryptoCommon $common*/
    public $common;
	/* @var GuzzleHttp\Client $client*/
    public $client;
	/* @var string $publicEndpoint*/
    public $publicEndpoint;
	/* @var string $endpoint*/
    public $endpoint;
	/* @var string $clientId*/
    public $clientId;
	/* @var string $clientSecret*/
    public $clientSecret;
	/* @var string $authClient*/
    public $authClient;

    /**
	 * It creates a new instance of the GoCryptoLogger, GoCryptoCommon, GuzzleHttp\Client and sets the
	 * publicEndpoint.
	 * 
	 * @param isTest If you want to use the test environment, set this to true.
	 */
	public function __construct($isTest = false) {
        $this->logger = new GoCryptoLogger();
        $this->common = new GoCryptoCommon();
        $this->client = new GuzzleHttp\Client();
        $this->publicEndpoint = $isTest ? 'https://public.api.staging.ellypos.io'
		 : 'https://public.api.ellypos.io';
    }

    /**
	 * > Sets the client ID and client secret for the application
	 * 
	 * @param clientId The client ID you received from Google when you registered your application.
	 * @param clientSecret The client secret you received from Google when you registered your
	 * application.
	 */
	public function setCredentials($clientId, $clientSecret) {
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
    }

    /**
	 * > The function `auth()` creates a new instance of the `GoCryptoAuth` class, and then calls the
	 * `getAccessToken()` function on that instance
	 * 
	 * @return The access token
	 */
	public function auth()
    {
        $this->authClient = new GoCryptoAuth($this->endpoint, $this->clientId, $this->clientSecret);
        return $this->authClient->getAccessToken();
    }

    /**
	 * It gets the API endpoint from the public API and sets it as the endpoint for the private API
	 * 
	 * @param host The hostname of the web shop.
	 * 
	 * @return The response is a JSON object containing the API endpoint and the API key.
	 */
	public function config($host) {
        try {
            $response = $this->client->request('GET', $this->publicEndpoint . '/config/web-shop/', [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'X-WEB-SHOP-HOST' => $host
                ]
            ]);

            $responseData = json_decode((string)$response->getBody(), true);
            $this->endpoint = $responseData['api_endpoint'];
            return $responseData;
        } catch (RequestException $e) {
            $this->logger->writeLog($e->getMessage());
            return $e->getMessage();
        }
    }

    /**
	 * It pairs a terminal with the server.
	 * 
	 * @param terminalId The terminal ID of the device you want to pair.
	 * @param otp The OTP you received from the terminal.
	 * @param serialNumber This is the serial number of the device. If you don't have one, you can
	 * generate a random one.
	 * 
	 * @return The response is a JSON object containing the following keys:
	 */
	public function pair($terminalId, $otp, $serialNumber = null) {
        try {
            $response = $this->client->request('POST', $this->endpoint . '/devices/pair/', [
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'otp' => $otp,
                    'terminal_id' => $terminalId,
                    'serial_number' => $serialNumber ? $serialNumber : $this->common->randomNumbers(12),
                ]
            ]);

            return json_decode((string)$response->getBody(), true);
        } catch (RequestException $e) {
            $this->logger->writeLog($e->getMessage());
            return $e->getMessage();
        }
    }

    /**
	 * It takes an array of data, sends it to the API, and returns the charge ID and redirect URL
	 * 
	 * @param data An array of data that contains the following keys:
	 * 
	 * @return The charge_id and redirect_url are being returned.
	 */
	public function generateCharge($data = []) {
        try {
            $language = $data['language'];

            $chargeData = [
                'shop_name' => $data['shop_name'],
                'shop_description' => $data['shop_description'],
                'order_number' => $data['order_number'],
                'amount' => $data['amount'],
                'customer_email' => $data['customer_email'],
                'callback_endpoint' => $data['callback_endpoint']
            ];

            if (array_key_exists('currency_code', $data)) {
                $chargeData['currency_code'] = $data['currency_code'];
            }

            if (array_key_exists('items', $data)) {
                $chargeData['items'] = $data['items'];
            }

            $response = $this->client->request('POST', $this->endpoint . '/payments/charge/', [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept-Language' => $language ? $language : 'en',
                    'Authorization' => 'Bearer ' . $this->authClient->accessToken
                ],
                'json' => $chargeData
            ]);

            $responseData = json_decode((string)$response->getBody(), true);

            return [
                'charge_id' => $responseData['charge_id'],
                'redirect_url' => $responseData['redirect_url']
            ];
        } catch (RequestException $e) {
            $this->logger->writeLog($e->getMessage());
            return [];
        }
    }

    /**
	 * It gets the payment methods for the device
	 * 
	 * @return An array of payment methods.
	 */
	public function getPaymentMethods()
    {
        try {
            $response = $this->client->request('GET', $this->endpoint . '/devices/payment-methods/', [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $this->authClient->accessToken
                ]
            ]);

            return json_decode((string)$response->getBody(), true);
        } catch (RequestException $e) {
            $this->logger->writeLog($e->getMessage());
            return [];
        }
    }
    
    /**
	 * It checks the status of a transaction by sending a GET request to the
	 * `/transactions/{transactionId}/status/` endpoint
	 * 
	 * @param transactionId The transaction ID returned by the createTransaction() method.
	 * 
	 * @return The status of the transaction.
	 */
	public function checkTransactionStatus($transactionId) {
        try {
            $response = $this->client->request('GET', $this->endpoint . '/transactions/' . $transactionId . '/status/', [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $this->authClient->accessToken
                ]
            ]);

            return json_decode((string)$response->getBody(), true)['status'];
        } catch (RequestException $e) {
            $this->logger->writeLog($e->getMessage());
            return null;
        }
    }
}
