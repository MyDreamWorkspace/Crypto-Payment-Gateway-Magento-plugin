<?php

namespace Eligmaltd\GoCryptoPay\Controller\Checkout;

use Eligmaltd\GoCryptoPay\Lib\GoCryptoPay;
use Eligmaltd\GoCryptoPay\Helper\Data;
use Magento\Framework\Controller\ResultFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Index extends \Eligmaltd\GoCryptoPay\Controller\AbstractCheckoutAction
{
    /* @var \Psr\Log\LoggerInterface $logger*/
    protected $logger;

    /**
     * This function is called when the class is instantiated. It sets up the class with the necessary
     * dependencies
     * 
     * @param \Magento\Framework\App\Action\Context context This is the context of the action.
     * @param \Psr\Log\LoggerInterface logger This is the logger interface.
     * @param \Magento\Checkout\Model\Session checkoutSession This is the session object that Magento
     * uses to store the order information.
     * @param \Magento\Sales\Model\OrderFactory orderFactory This is the order factory that will be
     * used to load the order.
     * @param Data dataHelper This is the class that contains the functions that will be used to
     * process the payment.
     * @param \Magento\Store\Model\StoreManagerInterface storeManager This is the Magento store
     * manager.
     * @param \Magento\Store\Api\Data\StoreInterface store The store object
     * @param \Magento\Framework\App\Config\ScopeConfigInterface scopeConfig This is the Magento 2
     * configuration object.
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        Data $dataHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Store\Api\Data\StoreInterface $store,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->_store = $store;
        $this->_storeManager = $storeManager;
        $this->logger = $logger;
        $this->scopeConfig = $scopeConfig;
        parent::__construct($context, $logger, $checkoutSession, $orderFactory, $dataHelper);
    }

    /**
     * It returns the name of the store that is associated with the website ID of 1
     * 
     * @return The name of the store.
     */
    public function getStoreName()
    {
        $storeManagerDataList = $this->_storeManager->getStores();
        foreach ($storeManagerDataList as $key => $value) {
            if ($value['website_id'] == 1) {
                return $value['name'];
            }
        }
    }

    /**
     * The function generates a charge for the order and redirects the user to the GoCryptoPay payment
     * page
     * 
     * @return The redirect URL to the GoCryptoPay payment page.
     */
    public function execute()
    {
        $order = $this->getOrder();
        $baseUrl = $this->_storeManager->getStore()->getBaseUrl(
        \Magento\Framework\UrlInterface::URL_TYPE_WEB);

        $storeId =  $this->_storeManager->getStore()->getId();
        $localeCode =  $this->scopeConfig->getValue('general/locale/code', 
        \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);

        $store_name = $this->getStoreName();
        if (!isset($order)) {
            return;
        }

        $config = $this->getDataHelper()->getScopeConfig();
        $host = $config->getValue('payment/gocrypto_pay/host', 
        \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $isSand = $config->getValue('payment/gocrypto_pay/is_sandbox', 
        \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $clientId = $config->getValue('payment/gocrypto_pay/client_id', 
        \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $clientSecret = $config->getValue('payment/gocrypto_pay/client_secret', 
        \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        $orderId = $order->getId();

        $ciphering = "AES-128-CTR";
        $options = 0;
        $encryption_iv = '1234567891011121';
        $encodeId = openssl_encrypt($orderId, $ciphering, $clientId, $options, $encryption_iv);

        $chargeData = [
            'shop_name' => $store_name,
            'shop_description' => $store_name,
            'language' => $localeCode,
            'order_number' => $orderId,
            'amount' => round($order->getTotalDue() * 100),
            'discount' => round($order->getDiscountAmount() * 100),
            'currency_code' => $order->getBaseCurrencyCode(),
            'customer_email' => $order->getBillingAddress()->getEmail(),
            'callback_endpoint' => $baseUrl.'gocryptopay/order/status?order_id='.$encodeId
        ];

        foreach ($order->getAllItems() as $itemID => $item) {
            $itemData = [
                'name' => $item->getName(),
                'quantity' => $item->getQtyOrdered(),
                'price' => round($item->getRowTotal() * 100),
                'tax' => round($item->getTaxAmount() * 100)
            ];

            $chargeData['items'][] = $itemData;
        }

        $gocryptoPay = new GoCryptoPay($isSand);
        $config = $gocryptoPay->config($host);

        try {
            if (!is_string($config)) {
                $gocryptoPay->setCredentials($clientId, $clientSecret);
                if ($gocryptoPay->auth()) {
                    $charge = $gocryptoPay->generateCharge($chargeData);
                    $redirectUrl = $charge['redirect_url'];
                    $this->getResponse()->setRedirect($redirectUrl);
                }
                else {
                    return;
                }
            }
            else {
                return;
            }
        }
        catch(Exception $e) {
            printf($e, 1);
            return;
        }
    }

}
