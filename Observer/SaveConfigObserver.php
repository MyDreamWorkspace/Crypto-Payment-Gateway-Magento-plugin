<?php
namespace Eligmaltd\GoCryptoPay\Observer;

use Eligmaltd\GoCryptoPay\Lib\GoCryptoPay;
use Magento\Framework\Event\Observer;
use Magento\Payment\Observer\AbstractDataAssignObserver;
use Psr\Log\LoggerInterface as Logger;

class SaveConfigObserver extends AbstractDataAssignObserver
{
    /* @var Psr\Log\LoggerInterface $logger*/
    protected $logger;
    /* @var \Magento\Config\Model\ResourceModel\Config $_resourceConfig*/
    protected $_resourceConfig;
    /* @var \Magento\Framework\App\Config\ReinitableConfigInterface $_appConfig*/
    protected $_appConfig;
    /* @var \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig*/
    public $scopeConfig;

    /**
     * This function is called when the class is instantiated. It takes in the parameters that are
     * passed to the class and assigns them to the class variables
     * 
     * @param Logger logger This is the logger object that you can use to log messages to the
     * system.log file.
     * @param \Magento\Framework\App\Config\ScopeConfigInterface scopeConfig This is the Magento 2
     * configuration object.
     * @param \Magento\Config\Model\ResourceModel\Config resourceConfig This is the class that handles
     * the saving of the config values.
     * @param \Magento\Framework\App\Config\ReinitableConfigInterface config The configuration object
     * that is used to get the configuration values.
     */
    public function __construct(
        Logger $logger,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Config\Model\ResourceModel\Config $resourceConfig,
        \Magento\Framework\App\Config\ReinitableConfigInterface $config
    ) {
        $this->logger = $logger;
        $this->scopeConfig = $scopeConfig;
        $this->_resourceConfig = $resourceConfig;
        $this->_appConfig = $config;
    }

    /**
     * If the client_id is not set, then we get the host, is_sandbox, otp, and terminal_id from the
     * config, create a new GoCryptoPay object, get the config, if the config is not a string, then we
     * pair the terminal and otp, and if the pair response is not a string, then we save the client_id
     * and client_secret to the config
     * 
     * @param Observer observer The observer object.
     */
    public function execute(Observer $observer)
    {
        if (!$this->scopeConfig->getValue('payment/gocrypto_pay/client_id', 
        \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
            $host = $this->scopeConfig->getValue('payment/gocrypto_pay/host', 
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $isSand = $this->scopeConfig->getValue('payment/gocrypto_pay/is_sandbox', 
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $otp = $this->scopeConfig->getValue('payment/gocrypto_pay/otp', 
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $terminalID = $this->scopeConfig->getValue('payment/gocrypto_pay/terminal_id', 
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

            $gocryptoPay = new GoCryptoPay($isSand);
            $config = $gocryptoPay->config($host);

            if (!is_string($config)) {
                $pairResponse = $gocryptoPay->pair($terminalID, $otp);
                if (!is_string($pairResponse)) {
                    $clientId = $pairResponse['client_id'];
                    $clientSecret = $pairResponse['client_secret'];

                    $this->_resourceConfig->saveConfig('payment/gocrypto_pay/client_id', $clientId);
                    $this->_resourceConfig->saveConfig('payment/gocrypto_pay/client_secret', $clientSecret);
                }
            }
            
            $this->_appConfig->reinit();
        }
    }
}
