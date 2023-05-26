<?php
namespace Eligmaltd\GoCryptoPay\Gateway\Request;

use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;

class VoidRequest implements BuilderInterface
{
    /* @var Magento\Payment\Gateway\ConfigInterface $config*/
    private $config;

    /**
     * > This function takes a `ConfigInterface` object as an argument and assigns it to the ``
     * property
     * 
     * @param ConfigInterface config This is the config object that is passed to the constructor of the
     * class.
     */
    public function __construct(
        ConfigInterface $config
    ) {
        $this->config = $config;
    }

    /**
     * It builds the request array for the gateway
     * 
     * @param array buildSubject This is an array of data that is passed to the gateway.
     * 
     * @return The return value is an array of key-value pairs that will be sent to the gateway.
     */
    public function build(array $buildSubject)
    {
        if (!isset($buildSubject['payment'])
            || !$buildSubject['payment'] instanceof PaymentDataObjectInterface
        ) {
            throw new \InvalidArgumentException('Payment data object should be provided');
        }

        $paymentDO = $buildSubject['payment'];

        $order = $paymentDO->getOrder();
        $payment = $paymentDO->getPayment();

        if (!$payment instanceof OrderPaymentInterface) {
            throw new \LogicException('Order payment should be provided.');
        }

        return [
            'TXN_TYPE' => 'V',
            'TXN_ID' => $payment->getLastTransId(),
            'MERCHANT_KEY' => $this->config->getValue(
                'merchant_gateway_key',
                $order->getStoreId()
            )
        ];
    }
}
