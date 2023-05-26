<?php

namespace Eligmaltd\GoCryptoPay\Controller\Checkout;

use Eligmaltd\GoCryptoPay\Lib\GoCryptoPay;
use Magento\Framework\Controller\ResultFactory;

class Prepare extends \Eligmaltd\GoCryptoPay\Controller\AbstractCheckoutAction
{
    /**
     * It returns a JSON object with the configuration of the GoCryptoPay payment gateway
     * 
     * @return The result is a JSON object with the following structure:
     * ```
     * {
     *     "result": {
     *         "host": "https://gocrypto.com",
     *         "api_key": "",
     *         "api_secret": "",
     *         "is_sandbox": false,
     *         "is_testnet": false,
     *         "is_
     */
    public function execute()
    {
        $config = $this->getDataHelper()->getScopeConfig();
        $host = $config->getValue('payment/gocrypto_pay/host', 
        \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $isSand = $config->getValue('payment/gocrypto_pay/is_sandbox', 
        \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $gocryptoPay = new GoCryptoPay($isSand);
        $config = $gocryptoPay->config($host);
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData(['result' => $config]);
        return $resultJson;
    }
}
