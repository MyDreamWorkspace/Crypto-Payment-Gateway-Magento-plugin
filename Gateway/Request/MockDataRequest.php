<?php
namespace Eligmaltd\GoCryptoPay\Gateway\Request;

use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Eligmaltd\GoCryptoPay\Gateway\Http\Client\ClientMock;

class MockDataRequest implements BuilderInterface
{
    public const FORCE_RESULT = 'FORCE_RESULT';

    /**
     * > If the transaction result is null, return success, otherwise return the transaction result
     * 
     * @param array buildSubject An array containing the payment data object.
     * 
     * @return The result of the transaction.
     */
    public function build(array $buildSubject)
    {
        if (!isset($buildSubject['payment'])
            || !$buildSubject['payment'] instanceof PaymentDataObjectInterface
        ) {
            throw new \InvalidArgumentException('Payment data object should be provided');
        }

        $paymentDO = $buildSubject['payment'];
        $payment = $paymentDO->getPayment();

        $transactionResult = $payment->getAdditionalInformation('transaction_result');
        return [
            self::FORCE_RESULT => $transactionResult === null
                ? ClientMock::SUCCESS
                : $transactionResult
        ];
    }
}
