<?php

namespace Eligmaltd\GoCryptoPay\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    public const CODE = 'Eligmaltd_GoCryptoPay';
    public const SECURE_TRANSACTION_TYPE_SUFFIX = '3-D';

    public const ADDITIONAL_INFO_KEY_STATUS           = 'status';
    public const ADDITIONAL_INFO_KEY_TRANSACTION_TYPE = 'type';
    public const ADDITIONAL_INFO_KEY_REDIRECT_URL     = 'redirect_url';
    public const ADDITIONAL_INFO_KEY_PAYMENT_METHOD   = 'payment_method_type';
    public const ADDITIONAL_INFO_KEY_TEST             = 'test';

    public const AUTHORIZE                            = 'authorization';
    public const PAYMENT                              = 'payment';
    public const CAPTURE                              = 'capture';
    public const VOID                                 = 'void';
    public const REFUND                               = 'refund';

    public const CREDIT_CARD                          = 'credit_card';
    public const CREDIT_CARD_HALVA                    = 'halva';
    public const ERIP                                 = 'erip';

    public const PENDING                              = 'pending';
    public const INCOMPLETE                           = 'incomplete';
    public const SUCCESSFUL                           = 'successful';
    public const FAILED                               = 'failed';
    public const ERROR                                = 'error';

    /* @var \Magento\Framework\ObjectManagerInterface $_objectManager*/
    protected $_objectManager;
    /* @var \Magento\Payment\Helper\Data $paymentData*/
    protected $_paymentData;
    /* @var \Magento\Store\Model\StoreManagerInterface $_storeManager*/
    protected $_storeManager;
    /* @var \Eligmaltd\GoCryptoPay\Model\ConfigFactory $_configFactory*/
    protected $_configFactory;
    /* @var \Magento\Framework\App\Config\ScopeConfigInterface $_scopeConfig*/
    protected $_scopeConfig;
    /* @var \Magento\Framework\Locale\ResolverInterface $_localeResolver*/
    protected $_localeResolver;
    /* @var \Magento\Directory\Model\RegionFactory $_regionFactory*/
    protected $_regionFactory;
    /* @var \Magento\Framework\Module\ModuleListInterface $_moduleList*/
    protected $_moduleList;

    /**
     * The constructor of the class.
     * 
     * @param \Magento\Framework\ObjectManagerInterface objectManager This is the object manager that
     * Magento uses to instantiate objects.
     * @param \Magento\Framework\App\Helper\Context context This is the context of the module.
     * @param \Magento\Payment\Helper\Data paymentData This is the payment helper class.
     * @param \Magento\Store\Model\StoreManagerInterface storeManager This is the Magento store
     * manager.
     * @param \Eligmaltd\GoCryptoPay\Model\ConfigFactory configFactory This is the factory class that
     * will be used to create the configuration object.
     * @param \Magento\Framework\Locale\ResolverInterface localeResolver This is used to get the locale
     * of the store.
     * @param \Magento\Directory\Model\RegionFactory regionFactory This is used to get the region name
     * from the region code.
     * @param \Magento\Framework\Module\ModuleListInterface moduleList This is used to get the module
     * version.
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Eligmaltd\GoCryptoPay\Model\ConfigFactory $configFactory,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Directory\Model\RegionFactory $regionFactory,
        \Magento\Framework\Module\ModuleListInterface $moduleList
    ) {
        $this->_objectManager = $objectManager;
        $this->_paymentData   = $paymentData;
        $this->_storeManager  = $storeManager;
        $this->_configFactory = $configFactory;
        $this->_localeResolver = $localeResolver;
        $this->_regionFactory = $regionFactory;
        $this->_moduleList = $moduleList;
        $this->_scopeConfig   = $context->getScopeConfig();
        parent::__construct($context);
    }

    /**
     * > This function returns an instance of the class that called it
     * 
     * @param objectManager This is the object manager.
     * 
     * @return An instance of the class that is being called.
     */
    public function getInstance($objectManager)
    {
        return $objectManager->create(get_class($this));
    }

    /**
     * It returns the object manager.
     * 
     * @return The object manager.
     */
    protected function getObjectManager()
    {
        return $this->_objectManager;
    }

    /**
     * It returns the store manager object.
     * 
     * @return The store manager object.
     */
    protected function getStoreManager()
    {
        return $this->_storeManager;
    }

    /**
     * It returns the config factory.
     * 
     * @return The config factory.
     */
    protected function getConfigFactory()
    {
        return $this->_configFactory;
    }

    /**
     * It returns the URL Builder.
     * 
     * @return The URL Builder object.
     */
    public function getUrlBuilder()
    {
        return $this->_urlBuilder;
    }

    /**
     * It returns the scope config.
     * 
     * @return The scope config object.
     */
    public function getScopeConfig()
    {
        return $this->_scopeConfig;
    }

    /**
     * It returns the locale resolver.
     * 
     * @return The locale resolver object.
     */
    protected function getLocaleResolver()
    {
        return $this->_localeResolver;
    }

    /**
     * 
     * 
     * @return The region factory.
     */
    public function getRegionFactory()
    {
        return $this->_regionFactory;
    }

    /**
     * It gets the object manager, then gets the product metadata interface, then gets the version
     * 
     * @return The Magento version.
     */
    public function getMagentoVersion()
    {
      return $this->getObjectManager()::get('Magento\Framework\App\ProductMetadataInterface')->getVersion();
    }

    /**
     * It returns the version of the module.
     * 
     * @return The version of the module.
     */
    public function getVersion()
    {
        return $this->_moduleList
             ->getOne(self::CODE)['setup_version'];
    }

    /**
     * It returns a URL for a given module, controller, and query parameters
     * 
     * @param moduleCode The module code, which is the same as the module name, but with underscores
     * instead of dashes.
     * @param controller The controller name
     * @param queryParams An array of key/value pairs that will be appended to the URL as query
     * parameters.
     * @param secure If you want to force the URL to be secure, set this to true.
     * @param storeId The store ID to use for the URL. If not set, the current store will be used.
     * 
     * @return The URL to the controller action.
     */
    public function getUrl($moduleCode, $controller, $queryParams = null, $secure = null, $storeId = null)
    {
        list($route, $module) = explode('_', $moduleCode);

        $path = sprintf("%s/%s/%s", $route, $module, $controller);

        $store = $this->getStoreManager()->getStore($storeId);
        $params = [
            "_store" => $store,
            "_secure" =>
                ($secure === null
                    ? $this->isStoreSecure($storeId)
                    : $secure
                )
        ];

        if (isset($queryParams) && is_array($queryParams)) {
            foreach ($queryParams as $queryKey => $queryValue) {
                $params[$queryKey] = $queryValue;
            }
        }

        return $this->getUrlBuilder()->getUrl(
            $path,
            $params
        );
    }

    /**
     * It returns the URL of the page that will be called by the payment gateway when the payment is
     * processed
     * 
     * @param moduleCode The module code of the payment method.
     * @param secure true/false, whether to use https or not
     * @param storeId The store ID to use. If not specified, the current store will be used.
     * 
     * @return The URL of the notification page.
     */
    public function getNotificationUrl($moduleCode, $secure = null, $storeId = null)
    {
        $store = $this->getStoreManager()->getStore($storeId);
        $params = [
            "_store" => $store,
            "_secure" =>
                ($secure === null
                    ? $this->isStoreSecure($storeId)
                    : $secure
                )
        ];

        return $this->getUrlBuilder()->getUrl(
            "begateway/ipn",
            $params
        );
    }

    /**
     * It returns the URL of the module's controller's action
     * 
     * @param moduleCode The module code of the module you want to redirect to.
     * @param returnAction The action to return to after the user has logged in.
     * 
     * @return The URL to the module's redirect action.
     */
    public function getReturnUrl($moduleCode, $returnAction)
    {
        return $this->getUrl(
            $moduleCode,
            "redirect",
            [
                "action" => $returnAction
            ]
        );
    }

    /**
     * It generates a unique hash.
     * 
     * @return A hash of a unique id.
     */
    protected function uniqHash()
    {
        return hash('sha256', uniqid(microtime().random_int(), true));
    }

    /**
     * It generates a unique hash for a transaction ID
     * 
     * @param orderId The order ID of the order you want to refund.
     */
    public function genTransactionId($orderId = null)
    {
        if (empty($orderId)) {
            return $this->uniqHash();
        }

        return sprintf(
            "%s_%s",
            $orderId.ToString(),
            $this->uniqHash()
        );
    }

    /**
     * It gets the value of a parameter from the transaction's additional information
     * 
     * @param transaction The transaction object
     * @param paramName The name of the parameter you want to get the value of.
     * 
     * @return The value of the parameter  from the transaction .
     */
    public function getTransactionAdditionalInfoValue($transaction, $paramName)
    {
        $transactionInformation = $transaction->getAdditionalInformation(
            \Magento\Sales\Model\Order\Payment\Transaction::RAW_DETAILS
        );

        if (is_array($transactionInformation) && isset($transactionInformation[$paramName])) {
            return $transactionInformation[$paramName];
        }

        return null;
    }

    /**
     * It returns the value of a specific parameter from the transaction additional info of a payment
     * 
     * @param \Magento\Payment\Model\InfoInterface payment The payment object
     * @param paramName The name of the parameter you want to get the value of.
     * 
     * @return The value of the parameter name passed in.
     */
    public function getPaymentAdditionalInfoValue(
        \Magento\Payment\Model\InfoInterface $payment,
        $paramName
    ) {
        $paymentAdditionalInfo = $payment->getTransactionAdditionalInfo();

        $rawDetailsKey = \Magento\Sales\Model\Order\Payment\Transaction::RAW_DETAILS;

        if (!array_key_exists($rawDetailsKey, $paymentAdditionalInfo)) {
            return null;
        }

        if (!array_key_exists($paramName, $paymentAdditionalInfo[$rawDetailsKey])) {
            return null;
        }

        return $paymentAdditionalInfo[$rawDetailsKey][$paramName];
    }

    /**
     * It returns the transaction type of a transaction
     * 
     * @param transaction The transaction object
     * 
     * @return The transaction type.
     */
    public function getTransactionTypeByTransaction($transaction)
    {
        return $this->getTransactionAdditionalInfoValue(
            $transaction,
            self::ADDITIONAL_INFO_KEY_TRANSACTION_TYPE
        );
    }

    /**
     * It returns the payment method used for a transaction
     * 
     * @param transaction The transaction object
     * 
     * @return The payment method used for the transaction.
     */
    public function getPaymentMethodByTransaction($transaction)
    {
        return $this->getTransactionAdditionalInfoValue(
            $transaction,
            self::ADDITIONAL_INFO_KEY_PAYMENT_METHOD
        );
    }

    /**
     * It creates a new instance of the `\Magento\Payment\Model\Config` class, and then sets the method
     * code on the instance
     * 
     * @param methodCode The method code of the payment method.
     * 
     * @return The method config for the given method code.
     */
    public function getMethodConfig($methodCode)
    {
        $parameters = [
            'params' => [
                $methodCode,
                $this->getStoreManager()->getStore()->getId()
            ]
        ];

        $config = $this->getConfigFactory()->create(
            $parameters
        );

        $config->setMethodCode($methodCode);

        return $config;
    }

    /**
     * > This function throws a WebApiException with the message and code of the given Exception
     * 
     * @param \Exception e The exception that was thrown
     */
    public function maskException(\Exception $e)
    {
        $this->throwWebApiException(
            $e->getMessage(),
            $e->getCode()
        );
    }

    /**
     * It creates a new WebApiException object with the given phrase, httpCode, and other default
     * parameters
     * 
     * @param phrase The error message
     * @param httpCode The HTTP status code to return.
     * 
     * @return A new instance of the Webapi Exception class.
     */
    public function createWebApiException(
        $phrase,
        $httpCode = \Magento\Framework\Webapi\Exception::HTTP_INTERNAL_ERROR
    ) {
        if (is_string($phrase)) {
            $phrase = new \Magento\Framework\Phrase($phrase);
        }

        return new \Magento\Framework\Webapi\Exception(
            $phrase,
            0,
            $httpCode,
            [],
            '',
            null,
            null
        );
    }

    /**
     * > This function creates a new WebApiException object and throws it
     * 
     * @param errorMessage The error message to be displayed to the user.
     * @param errorCode The error code that will be returned to the client.
     */
    public function throwWebApiException($errorMessage, $errorCode = 0)
    {
        $webApiException = $this->createWebApiException($errorMessage, $errorCode);

        throw $webApiException;
    }

    /**
     * It returns a transaction object if the transaction exists, otherwise it returns null
     * 
     * @param fieldValue The value of the field you're searching for.
     * @param fieldName The name of the field you want to search by.
     * 
     * @return The payment transaction object.
     */
    public function getPaymentTransaction($fieldValue, $fieldName = 'txn_id')
    {
        if (!isset($fieldValue) || empty($fieldValue)) {
            return null;
        }

        $transaction = $this->getObjectManager()::create(
            "\\Magento\\Sales\\Model\\Order\\Payment\\Transaction"
        )->load(
            $fieldValue,
            $fieldName
        );

        return ($transaction->getId() ? $transaction : null);
    }

    /**
     * It takes a gateway response object and returns an array of the response
     * 
     * @param response The response object from the gateway.
     * 
     * @return The response from the gateway is being returned.
     */
    public function getArrayFromGatewayResponse($response)
    {
        try {
            $arResponse = $response->getResponseArray();

            if (isset($arResponse['transaction'])) {

            $arResponse = $arResponse['transaction'];

            if (isset($arResponse['credit_card'])) {
                $arResponse['credit_card'] =
                $arResponse['credit_card']['first_1'] . ' xxxx ' .
                $arResponse['credit_card']['last_4'];

                if (isset($arResponse['credit_card']['sub_brand'])) {
                    $arResponse['credit_card_sub_brand'] =
                    $arResponse['credit_card']['sub_brand'];
                }

                if (isset($arResponse['credit_card']['product'])) {
                    $arResponse['credit_card_product'] =
                    $arResponse['credit_card']['product'];
                }
            }

            if (isset($arResponse['type'])) {
                $arResponse = array_merge($arResponse, $arResponse[$arResponse['type']]);
            }
        }

        if (isset($arResponse['checkout'])) {
            $arResponse = $arResponse['checkout'];
        }

        foreach ($arResponse as $p => $v) {
            if (!is_array($v)) {
                $transaction_details[$p] = (string)$v;
            }
        }

        } catch (Exception $e) {
            $transaction_details = [];
        }
        return $transaction_details;
    }

    /**
     * It returns true if the store is currently secure
     * 
     * @param storeId The store ID to check. If not specified, the current store will be used.
     */
    public function isStoreSecure($storeId = null)
    {
        $store = $this->getStoreManager()->getStore($storeId);
        return $store->isCurrentlySecure();
    }

    /**
     * It takes the response from the gateway and adds it to the transaction as an array
     * 
     * @param payment The payment object that you're working with.
     * @param responseObject The response object from the gateway.
     */
    public function setPaymentTransactionAdditionalInfo($payment, $responseObject)
    {
        $payment->setTransactionAdditionalInfo(
            \Magento\Sales\Model\Order\Payment\Transaction::RAW_DETAILS,
            $this->getArrayFromGatewayResponse(
                $responseObject
            )
        );
    }

    /**
     * It updates the transaction with the additional information from the response object
     * 
     * @param transactionId The transaction ID of the transaction you want to update.
     * @param responseObject The response object from the API call.
     * @param shouldCloseTransaction This is a boolean value that indicates whether the transaction
     * should be closed or not.
     */
    public function updateTransactionAdditionalInfo($transactionId, $responseObject, $shouldCloseTransaction = false)
    {
        $transaction = $this->getPaymentTransaction($transactionId);

        if (isset($transaction)) {
            $this->setTransactionAdditionalInfo(
                $transaction,
                $responseObject
            );

            if ($shouldCloseTransaction) {
                $transaction->setIsClosed(true);
            }

            $transaction->save();

            return true;
        }

        return false;
    }

    /**
     * It takes the response from the gateway and adds it to the transaction as an array
     * 
     * @param transaction The transaction object that you're setting the additional information on.
     * @param responseObject The response object from the gateway.
     */
    public function setTransactionAdditionalInfo($transaction, $responseObject)
    {
        $transaction
            ->setAdditionalInformation(
                \Magento\Sales\Model\Order\Payment\Transaction::RAW_DETAILS,
                $this->getArrayFromGatewayResponse(
                    $responseObject
                )
            );
    }

    /**
     * It sets the order state and status based on the state
     * 
     * @param order The order object
     * @param state The state of the order.
     */
    public function setOrderStatusByState($order, $state)
    {
        $order
            ->setState($state)
            ->setStatus(
                $order->getConfig()->getStateDefaultStatus(
                    $state
                )
            );
    }

    /**
     * It sets the order status to the status that is passed in as a parameter
     * 
     * @param order The order object
     * @param status The status of the order.
     * @param message The message to be displayed to the customer.
     */
    public function setOrderState($order, $status, $message = '')
    {
        switch ($status) {
            case self::SUCCESSFUL:
                $this->setOrderStatusByState(
                    $order,
                    \Magento\Sales\Model\Order::STATE_PROCESSING
                );
                $order->save();
                break;

            case self::INCOMPLETE:
            case self::PENDING:
                $this->setOrderStatusByState(
                    $order,
                    \Magento\Sales\Model\Order::STATE_PENDING_PAYMENT
                );
                $order->save();
                break;

            case self::FAILED:
            case self::ERROR:
                foreach ($order->getInvoiceCollection() as $invoice) {
                    $invoice->cancel();
                }
                $order
                    ->registerCancellation($message)
                    ->setCustomerNoteNotify(true)
                    ->save();
                break;
            default:
                $order->save();
                break;
        }
    }

    /**
     * It takes an order object and returns a string containing the order items and quantities
     * 
     * @param order The order object
     * @param lineSeparator The character(s) that will be used to separate each line item in the order
     * description.
     * 
     * @return The order description text.
     */
    public function buildOrderDescriptionText($order, $lineSeparator = PHP_EOL)
    {
        $orderDescriptionText = "";

        $orderItems = $order->getItems();

        foreach ($orderItems as $orderItem) {
            $separator = ($orderItem == end($orderItems)) ? '' : $lineSeparator;

            $orderDescriptionText .=
                $orderItem->getQtyOrdered() .
                ' x ' .
                $orderItem->getName() .
                $separator;
        }

        return $orderDescriptionText;
    }

    /**
     * It returns the text that is displayed in the admin panel when the user is creating a new
     * transaction.
     * 
     * @return The return value is a string.
     */
    public function buildOrderUsage()
    {
        return __("Magento 2 Transaction");
    }

    /**
     * It looks up a transaction by the last transaction ID of the payment object, and then looks up
     * the parent transaction of that transaction until it finds a transaction of the type specified in
     * the  array
     * 
     * @param payment The payment object
     * @param array transactionTypes An array of transaction types to look for.
     */
    public function lookUpPaymentTransaction($payment, array $transactionTypes)
    {
        $transaction = null;

        $lastPaymentTransactionId = $payment->getLastTransId();

        $transaction = $this->getPaymentTransaction(
            $lastPaymentTransactionId
        );

        while (isset($transaction)) {
            if (in_array($transaction->getTxnType(), $transactionTypes)) {
                break;
            }
            $transaction = $this->getPaymentTransaction(
                $transaction->getParentId(),
                'transaction_id'
            );
        }

        return $transaction;
    }

    /**
     * > This function looks up the authorization transaction for a given payment
     * 
     * @param payment The payment object
     * @param transactionTypes The type of transaction you're looking for.
     * 
     * @return The authorization transaction
     */
    public function lookUpAuthorizationTransaction(
        $payment, 
        $transactionTypes = [
            \Magento\Sales\Model\Order\Payment\Transaction::TYPE_AUTH
        ]
    ) {
        return $this->lookUpPaymentTransaction(
            $payment,
            $transactionTypes
        );
    }

    /**
     * > This function looks up a transaction for a given payment object
     * 
     * @param payment The payment object
     * @param transactionTypes An array of transaction types to look for.
     * 
     * @return The transaction object.
     */
    public function lookUpCaptureTransaction(
        $payment, 
        $transactionTypes = [
            \Magento\Sales\Model\Order\Payment\Transaction::TYPE_CAPTURE
        ]
    ) {
        return $this->lookUpPaymentTransaction(
            $payment,
            $transactionTypes
        );
    }

    /**
     * > This function looks up the payment transaction for the given payment object and transaction
     * types
     * 
     * @param payment The payment object
     * @param transactionTypes This is an array of transaction types that you want to look up.
     * 
     * @return The transaction that is being returned is the transaction that is being looked up.
     */
    public function lookUpVoidReferenceTransaction(
        $payment, 
        $transactionTypes = [
            \Magento\Sales\Model\Order\Payment\Transaction::TYPE_CAPTURE,
            \Magento\Sales\Model\Order\Payment\Transaction::TYPE_AUTH
        ]
    ) {
        return $this->lookUpPaymentTransaction(
            $payment,
            $transactionTypes
        );
    }

    /**
     * It returns an array of all the allowed currency codes in the store
     * 
     * @return An array of allowed currency codes.
     */
    public function getGlobalAllowedCurrencyCodes()
    {
        $allowedCurrencyCodes = $this->getScopeConfig()->getValue(
            \Magento\Directory\Model\Currency::XML_PATH_CURRENCY_ALLOW
        );

        return array_map(
            'trim',
            explode(
                ',',
                $allowedCurrencyCodes
            )
        );
    }

    /**
     * It takes an array of available currencies and returns an array of allowed currencies
     * 
     * @param array availableCurrenciesOptions This is an array of all the available currencies in the
     * system.
     */
    public function getGlobalAllowedCurrenciesOptions(array $availableCurrenciesOptions)
    {
        $allowedCurrenciesOptions = [];

        $allowedGlobalCurrencyCodes = $this->getGlobalAllowedCurrencyCodes();

        foreach ($availableCurrenciesOptions as $availableCurrencyOptions) {
            if (in_array($availableCurrencyOptions['value'], $allowedGlobalCurrencyCodes)) {
                $allowedCurrenciesOptions[] = $availableCurrencyOptions;
            }
        }
        return $allowedCurrenciesOptions;
    }

    /**
     * It takes an array of allowed local currencies and returns an array of allowed local currencies
     * that are also allowed globally
     * 
     * @param array allowedLocalCurrencies An array of currency codes that are allowed for the current
     * store.
     */
    public function getFilteredLocalAllowedCurrencies(array $allowedLocalCurrencies)
    {
        $result = [];
        $allowedGlobalCurrencyCodes = $this->getGlobalAllowedCurrencyCodes();

        foreach ($allowedLocalCurrencies as $allowedLocalCurrency) {
            if (in_array($allowedLocalCurrency, $allowedGlobalCurrencyCodes)) {
                $result[] = $allowedLocalCurrency;
            }
        }

        return $result;
    }

    /**
     * It gets the locale from the locale resolver, and returns the first two characters of the locale
     * 
     * @param default The default language code to use if the language code cannot be determined.
     * 
     * @return The first two characters of the locale code.
     */
    public function getLocale($default = 'en')
    {
        $languageCode = strtolower(
            $this->getLocaleResolver()->getLocale()
        );

        $languageCode = substr($languageCode, 0, 2);

        return $languageCode;
    }

    /**
     * > If the transaction type is a capture or payment and the payment method is a credit card, then
     * the transaction can be refunded
     * 
     * @param transaction The transaction object that you want to check.
     * 
     * @return A boolean value.
     */
    public function canRefundTransaction($transaction)
    {
        $refundableTransactions = [
            self::CAPTURE,
            self::PAYMENT
        ];

        $transactionType = $this->getTransactionTypeByTransaction(
            $transaction
        );

        $paymentMethod = $this->getPaymentMethodByTransaction($transaction);

        return (
          !empty($transactionType) &&
          in_array($transactionType, $refundableTransactions) &&
          $paymentMethod == self::CREDIT_CARD
        );
    }

    /**
     * If the method is configured to use all currencies, return the global list of allowed currencies.
     * Otherwise, return the list of allowed currencies for the method
     * 
     * @param methodCode The payment method code.
     * @param currencyCode The currency code of the current store.
     */
    public function isCurrencyAllowed($methodCode, $currencyCode)
    {
        $methodConfig = $this->getMethodConfig($methodCode);

        if (!$methodConfig->getAreAllowedSpecificCurrencies()) {
            $allowedMethodCurrencies = $this->getGlobalAllowedCurrencyCodes();
        } else {
            $allowedMethodCurrencies =
                $this->getFilteredLocalAllowedCurrencies(
                    $methodConfig->getAllowedCurrencies()
                );
        }

        return in_array($currencyCode, $allowedMethodCurrencies);
    }

    /**
     * If the needle is empty, return true. Otherwise, return whether the last characters of the
     * haystack are equal to the needle
     * 
     * @param haystack The string to search in
     * @param needle The string to search for.
     */
    public function getStringEndsWith($haystack, $needle)
    {
        $length = strlen($needle);
        if ($length == 0) {
            return true;
        }
        return (substr($haystack, -$length) === $needle);
    }

    /**
     * > Returns true if the transaction type ends with the suffix "3DSECURE"
     * 
     * @param transactionType The transaction type.
     */
    public function getIsTransaction3dSecure($transactionType)
    {
        return
            $this->getStringEndsWith(
                strtoupper($transactionType),
                self::SECURE_TRANSACTION_TYPE_SUFFIX
            );
    }

    /**
     * > If the response has a message, return it, otherwise return a generic error message
     * 
     * @param response The response object returned by the gateway.
     * 
     * @return The error message from the gateway response.
     */
    public function getErrorMessageFromGatewayResponse($response)
    {
        return
            (!empty($response->getMessage()))
                ? "{$response->getMessage()}"
                : __('An error has occurred while processing your request to the gateway');
    }
}
