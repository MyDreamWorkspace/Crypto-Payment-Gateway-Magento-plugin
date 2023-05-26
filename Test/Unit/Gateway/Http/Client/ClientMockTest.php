<?php

namespace Magento\SamplePaymentProvider\Test\Unit\Gateway\Http\Client;

use Magento\Payment\Gateway\Http\TransferInterface;
use Magento\Payment\Model\Method\Logger;
use Eligmaltd\GoCryptoPay\Gateway\Http\Client\ClientMock;

class ClientMockTest extends \PHPUnit_Framework_TestCase
{
    public const TXN_ID = 'fcd7f001e9274fdefb14bff91c799306';
    /* @var Magento\Payment\Model\Method\Logger $logger*/
    private $logger;
    /* @var Eligmaltd\GoCryptoPay\Gateway\Http\Client\ClientMock $clientMock*/
    private $clientMock;

    public function setUp()
    {
        $this->logger = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->clientMock = $this->getMockBuilder(ClientMock::class)
            ->setMethods(['generateTxnId'])
            ->setConstructorArgs([$this->logger])
            ->getMock();
    }

    public function testPlaceRequest(array $expectedRequest, array $expectedResponse, array $expectedHeaders)
    {
        $transferObject = $this->getMock(TransferInterface::class);
        $transferObject->expects(static::once())
            ->method('getBody')
            ->willReturn($expectedRequest);
        $transferObject->expects(static::once())
            ->method('getHeaders')
            ->willReturn($expectedHeaders);

        $this->clientMock->expects(static::once())
            ->method('generateTxnId')
            ->willReturn(self::TXN_ID);

        $this->logger->expects(static::once())
            ->method('debug')
            ->with(
                [
                    'request' => $expectedRequest,
                    'response' => $expectedResponse
                ]
            );

        static::assertEquals(
            $expectedResponse,
            $this->clientMock->placeRequest($transferObject)
        );
    }

    public function placeRequestDataProvider()
    {
        return [
            'success' => [
                'expectedRequest' => [
                    'TNX_TYPE' => 'A',
                    'INVOICE' => 1000
                ],
                'expectedResponse' => [
                    'RESULT_CODE' => ClientMock::SUCCESS,
                    'TXN_ID' => self::TXN_ID
                ],
                'expectedHeaders' => [
                    'force_result' => ClientMock::SUCCESS
                ]
            ],
            'fraud' => [
                'expectedRequest' => [
                    'TNX_TYPE' => 'A',
                    'INVOICE' => 1000
                ],
                'expectedResponse' => [
                    'RESULT_CODE' => ClientMock::FAILURE,
                    'TXN_ID' => self::TXN_ID,
                    'FRAUD_MSG_LIST' => [
                        'Stolen card',
                        'Customer location differs'
                    ]
                ],
                'expectedHeaders' => [
                    'force_result' => ClientMock::FAILURE
                ]
            ]
        ];
    }
}
