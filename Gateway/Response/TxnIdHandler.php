<?php
namespace Eligmaltd\GoCryptoPay\Gateway\Response;

use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Response\HandlerInterface;

class TxnIdHandler implements HandlerInterface
{
    public const TXN_ID = 'TXN_ID';

    /**
     * This function is called when the payment is successful. It sets the transaction id and sets the
     * transaction to be closed
     * 
     * @param array handlingSubject This is the array of parameters that is passed to the gateway.
     * @param array response The response from the gateway.
     */
    public function handle(array $handlingSubject, array $response)
    {
        if (!isset($handlingSubject['payment'])
            || !$handlingSubject['payment'] instanceof PaymentDataObjectInterface
        ) {
            throw new \InvalidArgumentException('Payment data object should be provided');
        }
        $paymentDO = $handlingSubject['payment'];
        $payment = $paymentDO->getPayment();
        $payment->setTransactionId($response[self::TXN_ID]);
        $payment->setIsTransactionClosed(false);
    }
}
