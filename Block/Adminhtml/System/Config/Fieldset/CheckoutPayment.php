<?php
namespace Eligmaltd\GoCryptoPay\Block\Adminhtml\System\Config\Fieldset;

class CheckoutPayment extends \Eligmaltd\GoCryptoPay\Block\Adminhtml\System\Config\Fieldset\Base\Payment
{
    /**
     * It returns the name of the class.
     * 
     * @return The name of the block class.
     */
    protected function getBlockHeadCssClass()
    {
        return "GoCryptoPayCheckout";
    }
}
