<?php

namespace Eligmaltd\GoCryptoPay\Model\Method;

use \Eligmaltd\GoCryptoPay\Model\Traits\OnlinePaymentMethod;
use \Eligmaltd\GoCryptoPay\Model\Traits\Logger;

class Checkout extends \Magento\Payment\Model\Method\AbstractMethod
{
    public const CODE = 'gocrypto_pay';
    /* @var string $_code*/
    protected $_code = self::CODE;
    /* @var bool $_canOrder*/ 
    protected $_canOrder                    = true;
    /* @var bool $_isGateway*/ 
    protected $_isGateway                   = true;
    /* @var bool $_canCapture*/ 
    protected $_canCapture                  = true;
    /* @var bool $_canCapturePartial*/ 
    protected $_canCapturePartial           = true;
    /* @var bool $_canRefund*/ 
    protected $_canRefund                   = true;
    /* @var bool $_canCancelInvoice*/ 
    protected $_canCancelInvoice            = true;
    /* @var bool $_canVoid*/ 
    protected $_canVoid                     = true;
    /* @var bool $_canRefundInvoicePartial*/ 
    protected $_canRefundInvoicePartial     = true;
    /* @var bool $_canAuthorize*/ 
    protected $_canAuthorize                = true;
    /* @var bool $_isInitializeNeeded*/ 
    protected $_isInitializeNeeded          = false;

    /**
     * It returns the logger object.
     * 
     * @return The logger object.
     */
    protected function getLogger()
    {
        return $this->_logger;
    }

    /**
     * The constructor of the class.
     * 
     * @param \Magento\Framework\Model\Context context The context of the module.
     * @param \Magento\Framework\App\Action\Context actionContext This is the context of the action.
     * @param \Magento\Framework\Registry registry Magento's registry object
     * @param \Magento\Framework\Api\ExtensionAttributesFactory extensionFactory This is used to create
     * extension attributes.
     * @param \Magento\Framework\Api\AttributeValueFactory customAttributeFactory This is used to
     * create custom attributes for the payment method.
     * @param \Magento\Payment\Helper\Data paymentData This is the payment helper class.
     * @param \Magento\Framework\App\Config\ScopeConfigInterface scopeConfig This is the Magento
     * configuration object.
     * @param \Magento\Payment\Model\Method\Logger logger This is the logger object that will be used
     * to log messages.
     * @param \Magento\Store\Model\StoreManagerInterface storeManager This is the Magento store
     * manager.
     * @param \Magento\Checkout\Model\Session checkoutSession This is the session object that contains
     * the order information.
     * @param \Eligmaltd\GoCryptoPay\Helper\Data moduleHelper This is the helper class for the module.
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource resource This is the resource
     * model for the payment method.
     * @param \Magento\Framework\Data\Collection\AbstractDb resourceCollection This is the collection
     * class for the model.
     * @param array data This is an array of data that is passed to the constructor.
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\App\Action\Context $actionContext,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Payment\Model\Method\Logger  $logger,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Eligmaltd\GoCryptoPay\Helper\Data $moduleHelper,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger,
            $resource,
            $resourceCollection,
            $data
        );
        $this->_actionContext = $actionContext;
        $this->_storeManager = $storeManager;
        $this->_checkoutSession = $checkoutSession;
        $this->_moduleHelper = $moduleHelper;

        $this->_logger = $logger;

        $this->_configHelper =
            $this->getModuleHelper()->getMethodConfig(
                $this->getCode()
            );
    }

    /**
     * The function returns the payment action for the payment method
     * 
     * @return The payment action.
     */
    public function getConfigPaymentAction()
    {
        return \Magento\Payment\Model\Method\AbstractMethod::ACTION_ORDER;
    }

    /**
     * It returns an array of transaction types that are selected in the admin panel
     * 
     * @return The selected transaction types.
     */
    public function getCheckoutTransactionTypes()
    {
        $selected_types = $this->getConfigHelper()->getTransactionTypes();

        return $selected_types;
    }

    /**
     * It returns an array of payment method types that are selected in the admin panel
     * 
     * @return The selected payment method types.
     */
    public function getCheckoutPaymentMethodTypes()
    {
        $selected_types = $this->getConfigHelper()->getPaymentMethodTypes();

        return $selected_types;
    }

    /**
     * The authorize function is used to authorize a payment
     * 
     * @param \Magento\Payment\Model\InfoInterface payment The payment object
     * @param amount The amount to be authorized.
     * 
     * @return The method returns the payment object.
     */
    public function authorize(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        if (!$this->canAuthorize()) {
            throw new \Magento\Framework\Exception\LocalizedException(__('The authorize action is not available.'));
        }
        return $this;
    }

    /**
     * The capture function is not available
     * 
     * @param \Magento\Payment\Model\InfoInterface payment The payment object
     * @param amount The amount to be captured.
     * 
     * @return The capture method is being returned.
     */
    public function capture(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        if (!$this->canCapture()) {
            throw new \Magento\Framework\Exception\LocalizedException(__('The capture action is not available.'));
        }
        return $this;
    }
    
    /**
     * If the payment method can be refunded, then return the payment method.
     * 
     * @param \Magento\Payment\Model\InfoInterface payment The payment object
     * @param amount The amount to be refunded.
     * 
     * @return The method is returning the object itself.
     */
    public function refund(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        if (!$this->canRefund()) {
            throw new \Magento\Framework\Exception\LocalizedException(__('The refund action is not available.'));
        }
        return $this;
    }
}
