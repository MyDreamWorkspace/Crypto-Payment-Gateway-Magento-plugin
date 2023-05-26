<?php
namespace Eligmaltd\GoCryptoPay\Gateway\Request;

use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Request\BuilderInterface;

class AuthorizationRequest implements BuilderInterface
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
     * It takes the order object and returns an array of data that will be sent to the payment gateway
     * 
     * @param array buildSubject This is the array of parameters that is passed to the build method.
     * 
     * @return The return value is an array of data that will be sent to the payment gateway.
     */
    public function build(array $buildSubject)
    {
        if (!isset($buildSubject['payment'])
            || !$buildSubject['payment'] instanceof PaymentDataObjectInterface
        ) {
            throw new \InvalidArgumentException('Payment data object should be provided');
        }

        $payment = $buildSubject['payment'];
        $order = $payment->getOrder();
        $address = $order->getShippingAddress();

        return [
            'TXN_TYPE' => 'A',
            'INVOICE' => $order->getOrderIncrementId(),
            'AMOUNT' => $order->getGrandTotalAmount(),
            'CURRENCY' => $order->getCurrencyCode(),
            'EMAIL' => $address->getEmail(),
            'MERCHANT_KEY' => $this->config->getValue(
                'merchant_gateway_key',
                $order->getStoreId()
            )
        ];
    }
}
