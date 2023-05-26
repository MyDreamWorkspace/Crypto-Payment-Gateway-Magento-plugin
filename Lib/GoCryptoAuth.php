<?php

namespace Eligmaltd\GoCryptoPay\Lib;

use GuzzleHttp;
use GuzzleHttp\Exception\RequestException;

class GoCryptoAuth 
{
	/* @var GoCryptoLogger $logger*/
    public $logger;
	/* @var GoCryptoCommon $common*/
    public $common;
	/* @var GuzzleHttp\Client $client*/
    public $client;
	/* @var string $endpoint*/
    public $endpoint;
	/* @var string $clientId*/
    public $clientId;
	/* @var string $clientSecret*/
    public $clientSecret;
	/* @var string $accessToken*/
    public $accessToken;

    /**
	 * This function creates a new instance of the GoCrypto class
	 * 
	 * @param endpoint The URL of the GoCrypto API.
	 * @param clientID The client ID you received from GoCrypto when you registered your application.
	 * @param clientSecret The client secret you received from GoCrypto.
	 */
	public function __construct(
		$endpoint, 
		$clientID, 
		$clientSecret
	) {
        $this->logger = new GoCryptoLogger();
        $this->common = new GoCryptoCommon();
        $this->client = new GuzzleHttp\Client();

        $this->endpoint = $endpoint;
        $this->clientId = $clientID;
        $this->clientSecret = $clientSecret;
    }

    /**
	 * It sends a POST request to the endpoint with the client id and client secret as the body
	 * 
	 * @return The access token is being returned.
	 */
	public function getAccessToken()
    {
        try {
            $response = $this->client->request('POST', $this->endpoint . '/auth/token/', [
                'headers' => [
                    'Content-Type' => 'application/json'
                ],
                'json' => [
                    'grant_type' => 'client_credentials',
                    'client_id' => $this->clientId,
                    'client_secret' => $this->clientSecret
                ]
            ]);

            $responseData = json_decode((string)$response->getBody(), true);
            $this->accessToken = $responseData['access_token'];
            return $this->accessToken;
        } catch (RequestException $e) {
            $this->logger->writeLog($e->getMessage());
            return null;
        }
    }
}
