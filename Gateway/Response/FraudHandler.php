<?php
namespace Eligmaltd\GoCryptoPay\Gateway\Response;

use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Sales\Model\Order\Payment;

class FraudHandler implements HandlerInterface
{
    public const FRAUD_MSG_LIST = 'FRAUD_MSG_LIST';

    /**
     * This function is called when the payment is in a pending state. It sets the payment to be in a
     * pending state and sets the fraud message list to be the response from the gateway
     * 
     * @param array handlingSubject The payment data object that is being processed.
     * @param array response The response from the gateway.
     * 
     * @return The response from the gateway.
     */
    public function handle(array $handlingSubject, array $response)
    {
        if (!isset($response[self::FRAUD_MSG_LIST]) || !is_array($response[self::FRAUD_MSG_LIST])) {
            return;
        }

        if (!isset($handlingSubject['payment'])
            || !$handlingSubject['payment'] instanceof PaymentDataObjectInterface
        ) {
            throw new \InvalidArgumentException('Payment data object should be provided');
        }

        $paymentDO = $handlingSubject['payment'];
        $payment = $paymentDO->getPayment();

        $payment->setAdditionalInformation(
            self::FRAUD_MSG_LIST,
            (array)$response[self::FRAUD_MSG_LIST]
        );

        $payment->setIsTransactionPending(true);
        $payment->setIsFraudDetected(true);
    }
}
