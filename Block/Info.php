<?php
namespace Eligmaltd\GoCryptoPay\Block;

use Magento\Framework\Phrase;
use Magento\Payment\Block\ConfigurableInfo;
use Eligmaltd\GoCryptoPay\Gateway\Response\FraudHandler;

class Info extends ConfigurableInfo
{
    /**
     * It returns the label of the field.
     * 
     * @param field The name of the field to be displayed.
     * 
     * @return The label for the field.
     */
    protected function getLabel($field)
    {
        return __($field);
    }

    /**
     * It takes the value of the field and returns a string representation of it
     * 
     * @param field The name of the field to be displayed.
     * @param value The value of the field.
     * 
     * @return The value of the field.
     */
    protected function getValueView($field, $value)
    {
        switch ($field) {
            case FraudHandler::FRAUD_MSG_LIST:
                return implode('; ', $value);
        }
        return parent::getValueView($field, $value);
    }
}
