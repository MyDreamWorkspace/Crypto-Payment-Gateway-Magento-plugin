<?php
namespace Eligmaltd\GoCryptoPay\Test\Unit\Gateway\Request;

use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Model\InfoInterface;
use Magento\Sales\Model\Order\Payment;
use Eligmaltd\GoCryptoPay\Gateway\Http\Client\ClientMock;
use Eligmaltd\GoCryptoPay\Gateway\Request\MockDataRequest;

class MockDataRequestTest extends \PHPUnit_Framework_TestCase
{
    public function testBuild($forceResultCode, $transactionResult)
    {
        $expectation = [
            MockDataRequest::FORCE_RESULT => $forceResultCode
        ];

        $paymentDO = $this->getMock(PaymentDataObjectInterface::class);
        $paymentModel = $this->getMock(InfoInterface::class);

        $paymentDO->expects(static::once())
            ->method('getPayment')
            ->willReturn($paymentModel);

        $paymentModel->expects(static::once())
            ->method('getAdditionalInformation')
            ->with('transaction_result')
            ->willReturn(
                $transactionResult
            );

        $request = new MockDataRequest();

        static::assertEquals(
            $expectation,
            $request->build(['payment' => $paymentDO])
        );
    }

    public function transactionResultsDataProvider()
    {
        return [
            [
                'forceResultCode' => ClientMock::SUCCESS,
                'transactionResult' => null
            ],
            [
                'forceResultCode' => ClientMock::SUCCESS,
                'transactionResult' => ClientMock::SUCCESS
            ],
            [
                'forceResultCode' => ClientMock::FAILURE,
                'transactionResult' => ClientMock::FAILURE
            ]
        ];
    }
}
