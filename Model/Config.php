<?php
namespace Eligmaltd\GoCryptoPay\Model;

use Magento\Store\Model\ScopeInterface;

class Config implements \Magento\Payment\Model\Method\ConfigInterface
{
    /* @var string $_methodCode */
    protected $_methodCode;
    /* @var int $_storeId */
    protected $_storeId;
    /* @var string $pathPattern */
    protected $pathPattern;
    /* @var \Magento\Framework\App\Config\ScopeConfigInterface $_scopeConfig */
    protected $_scopeConfig;

    /**
     * The constructor function is used to create an instance of the class
     * 
     * @param \Magento\Framework\App\Config\ScopeConfigInterface scopeConfig This is the scope config
     * object.
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->_scopeConfig = $scopeConfig;
    }

    /**
     * It returns the scope config.
     * 
     * @return The scope config object.
     */
    protected function getScopeConfig()
    {
        return $this->_scopeConfig;
    }

   /**
    * It returns the value of the private variable .
    * 
    * @return The method code.
    */
    public function getMethodCode()
    {
        return $this->_methodCode;
    }

    /**
     * 
     * 
     * @param storeId The store ID to use for the query.
     */
    public function setStoreId($storeId)
    {
        $this->_storeId = (int)$storeId;
        return $this;
    }

    /**
     * It returns the value of the configuration setting with the key `` from the configuration
     * scope ``
     * 
     * @param key The name of the configuration value you want to get.
     * @param storeId The store ID to get the value for.
     * 
     * @return The value of the config path.
     */
    public function getValue($key, $storeId = null)
    {
        switch ($key) {
            default:
                $underscored = strtolower(preg_replace('/(.)([A-Z])/', "$1_$2", $key));
                $path = $this->getSpecificConfigPath($underscored);
                if ($path !== null) {
                    $value = $this->getScopeConfig()->getValue(
                        $path,
                        ScopeInterface::SCOPE_STORE,
                        $this->_storeId
                    );
                    return $value;
                }
        }
        return null;
    }

    /**
     * It sets the method code.
     * 
     * @param methodCode The payment method code.
     */
    public function setMethodCode($methodCode)
    {
        $this->_methodCode = $methodCode;
    }

    /**
     * 
     * 
     * @param pathPattern The path pattern to match against.
     */
    public function setPathPattern($pathPattern)
    {
        $this->pathPattern = $pathPattern;
    }

    /**
     * It returns a string that is the path to the config value for the given field name
     * 
     * @param fieldName The name of the field you want to get the value for.
     * 
     * @return The path to the config file.
     */
    protected function getSpecificConfigPath($fieldName)
    {
        if ($this->pathPattern) {
            return sprintf($this->pathPattern, $this->_methodCode, $fieldName);
        }

        return "payment/{$this->_methodCode}/{$fieldName}";
    }

    /**
     * > If the shop ID, shop key, and transaction types are not empty, then the API is available
     * 
     * @param methodCode The payment method code.
     * 
     * @return The method is returning a boolean value.
     */
    public function isApiAvailable($methodCode = null)
    {
        return !empty($this->getShopId()) &&
               !empty($this->getShopKey()) &&
               !empty($this->getTransactionTypes());
    }

    /**
     * If the method is active and the API is available, then the method is available.
     * 
     * @param methodCode The method code of the payment method you want to check.
     */
    public function isMethodAvailable($methodCode = null)
    {
        return $this->isMethodActive($methodCode) &&
               $this->isApiAvailable($methodCode);
    }

    /**
     * > Returns true if the method is active
     * 
     * @param methodCode The method code of the payment method you want to check.
     */
    public function isMethodActive($methodCode = null)
    {
        $methodCode = $methodCode?: $this->_methodCode;

        return $this->isFlagChecked($methodCode, 'active');
    }

    /**
     * It returns true if the value of the specified config path is set to 1, otherwise it returns
     * false
     * 
     * @param methodCode The payment method code.
     * @param name The name of the flag you want to check.
     * 
     * @return A boolean value.
     */
    public function isFlagChecked($methodCode, $name)
    {
        $methodCode = $methodCode?: $this->_methodCode;

        return $this->getScopeConfig()->isSetFlag(
            "payment/{$methodCode}/{$name}",
            ScopeInterface::SCOPE_STORE,
            $this->_storeId
        );
    }

    /**
     * It returns the value of the shop_id column in the database.
     * 
     * @return The shop_id value from the database.
     */
    public function getShopId()
    {
        return $this->getValue('shop_id');
    }

    /**
     * It returns the value of the shop_key column in the database.
     * 
     * @return The value of the key 'shop_key'
     */
    public function getShopKey()
    {
        return $this->getValue('shop_key');
    }

    /**
     * It returns the value of the domain_gateway column in the database.
     * 
     * @return The domain gateway.
     */
    public function getDomainGateway()
    {
        return $this->getValue('domain_gateway');
    }

    /**
     * It returns the value of the domain_checkout column in the database.
     * 
     * @return The value of the key 'domain_checkout' in the array ->_data.
     */
    public function getDomainCheckout()
    {
        return $this->getValue('domain_checkout');
    }

    /**
     * It returns the value of the title field in the database
     * 
     * @return The title of the checkout page.
     */
    public function getCheckoutTitle()
    {
        return $this->getValue('title');
    }

    /**
     * It takes a comma separated string and returns an array of the values
     * 
     * @return An array of transaction types.
     */
    public function getTransactionTypes()
    {
        return
            array_map(
                'trim',
                explode(
                    ',',
                    $this->getValue('transaction_types')
                )
            );
    }

    /**
     * It takes a comma-separated string of payment method types, and returns an array of those payment
     * method types
     */
    public function getPaymentMethodTypes()
    {
        return
            array_map(
                'trim',
                explode(
                    ',',
                    $this->getValue('payment_method_types')
                )
            );
    }

    /**
     * It returns the value of the order_status column in the database
     * 
     * @return The value of the order_status column in the database.
     */
    public function getOrderStatusNew()
    {
        return $this->getValue('order_status');
    }

    /**
     * Returns true if the method is allowed to use specific currencies.
     */
    public function getAreAllowedSpecificCurrencies()
    {
        return $this->isFlagChecked($this->_methodCode, 'allow_specific_currency');
    }

    /**
     * It returns an array of currencies that are allowed to be used with this payment method
     */
    public function getAllowedCurrencies()
    {
        return array_map(
            'trim',
            explode(
                ',',
                $this->getValue('specific_currencies')
            )
        );
    }

    /**
     * It returns the value of the test_mode variable.
     * 
     * @return The value of the test_mode key in the ->_data array.
     */
    public function getTestMode()
    {
        return $this->getValue('test_mode');
    }

    /**
     * It returns the value of the host.
     * 
     * @return The host value from the array.
     */
    public function getHost()
    {
        return $this->getValue('host');
    }

    /**
     * It returns the value of the otp column in the database.
     * 
     * @return The OTP value.
     */
    public function getOTP()
    {
        return $this->getValue('otp');
    }

    /**
     * It returns the value of the terminal_id column in the database.
     * 
     * @return The terminal ID.
     */
    public function getTerminalID()
    {
        return $this->getValue('terminal_id');
    }
    
    /**
     * It returns a boolean value of the debug value.
     * 
     * @return A boolean value of the debug setting.
     */
    public function getDebug()
    {
        return (bool)$this->getValue('debug');
    }
}
