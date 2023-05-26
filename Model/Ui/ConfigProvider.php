<?php

namespace Eligmaltd\GoCryptoPay\Model\Ui;

use Eligmaltd\GoCryptoPay\Lib\GoCryptoPay;
use Magento\Checkout\Model\ConfigProviderInterface;
use Eligmaltd\GoCryptoPay\Gateway\Http\Client\ClientMock;

class ConfigProvider implements ConfigProviderInterface
{
    public const CODE = 'gocrypto_pay';
    
    /**
     * The function is called when the class is instantiated. It takes two parameters, the scopeConfig
     * and the storeManager. It then assigns the storeManager to the class variable _storeManager and
     * the scopeConfig to the class variable scopeConfig
     * 
     * @param \Magento\Framework\App\Config\ScopeConfigInterface scopeConfig This is the Magento 2
     * configuration object.
     * @param \Magento\Store\Model\StoreManagerInterface storeManager This is the class that handles
     * the store information.
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->_storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * It returns the configuration of the payment method.
     * 
     * @return The return is a JSON object that contains the configuration for the GoCryptoPay payment
     * method.
     */
    public function getConfig()
    {
        $host = $this->scopeConfig->getValue('payment/gocrypto_pay/host', 
        \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $isSand = $this->scopeConfig->getValue('payment/gocrypto_pay/is_sandbox', 
        \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        $storeId =  $this->_storeManager->getStore()->getId();
        $localeCode =  $this->scopeConfig->getValue('general/locale/code', 
        \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
        $gocrypto_pay = new GoCryptoPay($isSand);
        $config = $gocrypto_pay->config($host);

        return [
            'payment' => [
                self::CODE => [
                    'transactionResults' => [
                        ClientMock::SUCCESS => __('Success'),
                        ClientMock::FAILURE => __('Fraud')
                    ],
                    'prepareConfig' => $config,
                    'currentLocaleCode' => $localeCode
                ]
            ]
        ];
    }
}
