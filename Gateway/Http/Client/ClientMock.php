<?php
namespace Eligmaltd\GoCryptoPay\Gateway\Http\Client;

use Magento\Payment\Gateway\Http\ClientInterface;
use Magento\Payment\Gateway\Http\TransferInterface;
use Magento\Payment\Model\Method\Logger;

class ClientMock implements ClientInterface
{
    public const SUCCESS = 1;
    public const FAILURE = 0;

    /* @var Arrary $results*/
    private $results = [
        self::SUCCESS,
        self::FAILURE
    ];

    /* @var Magento\Payment\Model\Method\Logger $logger*/
    private $logger;

    /**
     * > This function takes a Logger object as an argument and assigns it to the  property
     * 
     * @param Logger logger This is the logger object that we will use to log messages.
     */
    public function __construct(
        Logger $logger
    ) {
        $this->logger = $logger;
    }

    /**
     * > This function takes a `TransferInterface` object, gets a result code from it, generates a
     * response for that code, and then logs the request and response
     * 
     * @param TransferInterface transferObject The object that contains the request data.
     * 
     * @return A response object.
     */
    public function placeRequest(TransferInterface $transferObject)
    {
        $response = $this->generateResponseForCode(
            $this->getResultCode(
                $transferObject
            )
        );

        $this->logger->debug(
            [
                'request' => $transferObject->getBody(),
                'response' => $response
            ]
        );

        return $response;
    }

    /**
     * It returns an array of values based on the result code passed in
     * 
     * @param resultCode The result code you want to generate.
     * 
     * @return An array of data.
     */
    protected function generateResponseForCode($resultCode)
    {
        return array_merge(
            [
                'RESULT_CODE' => $resultCode,
                'TXN_ID' => $this->generateTxnId()
            ],
            $this->getFieldsBasedOnResponseType($resultCode)
        );
    }

    /**
     * It generates a random number between 0 and 1000, then hashes it using the SHA256 algorithm
     * 
     * @return A random number between 0 and 1000 hashed with sha256.
     */
    protected function generateTxnId()
    {
        return hash('sha256', random_int(0, 1000));
    }

    /**
     * > It returns a random result code from the `` array
     * 
     * @param TransferInterface transfer The transfer object that contains the request and response.
     * 
     * @return A random result code.
     */
    private function getResultCode(TransferInterface $transfer)
    {
        $headers = $transfer->getHeaders();

        if (isset($headers['force_result'])) {
            return (int)$headers['force_result'];
        }

        return $this->results[random_int(0, 1)];
    }

    /**
     * > If the result code is `FAILURE`, return an array with a key of `FRAUD_MSG_LIST` and a value of
     * an array with two strings. Otherwise, return an empty array
     * 
     * @param resultCode The result code of the transaction.
     * 
     * @return An array of fields based on the result code.
     */
    private function getFieldsBasedOnResponseType($resultCode)
    {
        switch ($resultCode) {
            case self::FAILURE:
                return [
                    'FRAUD_MSG_LIST' => [
                        'Stolen card',
                        'Customer location differs'
                    ]
                ];
        }
        return [];
    }
}
