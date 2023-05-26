<?php
namespace Eligmaltd\GoCryptoPay\Model\Traits;
use Magento\Framework\Validator\Exception as ValidatorException;

trait OnlinePaymentMethod
{
    /* @var string $_configHelper*/
    protected $_configHelper;
    /* @var string $_moduleHelper*/
    protected $_moduleHelper;
    /* @var string $_actionContext*/
    protected $_actionContext;
    /* @var string $_storeManager*/
    protected $_storeManager;
    /* @var string $_urlBuilder*/
    protected $_urlBuilder;
    /* @var string $_checkoutSession*/
    protected $_checkoutSession;
    /* @var string $_transactionManager*/
    protected $_transactionManager;

    /**
     * It returns the value of the protected variable _configHelper.
     * 
     * @return The config helper.
     */
    protected function getConfigHelper()
    {
        return $this->_configHelper;
    }

    /**
     * It returns the module helper.
     * 
     * @return The module helper.
     */
    protected function getModuleHelper()
    {
        return $this->_moduleHelper;
    }

    /**
     * > This function returns the action context
     * 
     * @return The action context.
     */
    protected function getActionContext()
    {
        return $this->_actionContext;
    }

    /**
     * > This function returns the message manager
     * 
     * @return The message manager.
     */
    protected function getMessageManager()
    {
        return $this->getActionContext()->getMessageManager();
    }

    /**
     * It returns the store manager.
     * 
     * @return The store manager object.
     */
    protected function getStoreManager()
    {
        return$this->_storeManager;
    }

    /**
     * It returns the URL builder.
     * 
     * @return The URL Builder object.
     */
    protected function getUrlBuilder()
    {
        return $this->_urlBuilder;
    }

    /**
     * It returns the checkout session.
     * 
     * @return The checkout session object.
     */
    protected function getCheckoutSession()
    {
        return $this->_checkoutSession;
    }

    /**
     * > This function returns the transaction manager
     * 
     * @return The transaction manager.
     */
    protected function getTransactionManager()
    {
        return $this->_transactionManager;
    }

    /**
     * It processes a transaction of a given type (capture, refund, etc.) using the BeGateway API
     * 
     * @param transactionType the type of transaction you want to perform.
     * @param \Magento\Payment\Model\InfoInterface payment the payment object
     * @param data array of parameters
     * 
     * @return The response object.
     */
    protected function processReferenceTransaction(
        $transactionType,
        \Magento\Payment\Model\InfoInterface $payment,
        $data
    ) {
        $transactionType = ucfirst(
            strtolower(
                $transactionType
            )
        );

        $this->getConfigHelper()->initGatewayClient();
        $helper = $this->getModuleHelper();

        $begateway = "\\BeGateway\\{$transactionType}Operation";
        $begateway = new $begateway;

        $begateway->setParentUid($data['reference_id']);
        $begateway->money->setAmount($data['amount']);
        $begateway->money->setCurrency($data['currency']);

        if (strtolower($transactionType) == $helper::REFUND) {
            $begateway->setReason($data['reason']);
        }

        $responseObject = $begateway->submit();

        if (!$responseObject->isSuccess()) {
            throw new \ValidatorException(
                __('%1 operation error. Reason: %2',
                    $transactionType,
                    $responseObject->getMessage()
                )
            );
        }
          
        $payment
            ->setTransactionId(
                $responseObject->getUid()
            )
            ->setParentTransactionId(
                $data['reference_id']
            )
            ->setShouldCloseParentTransaction(
                true
            )
            ->setIsTransactionPending(
                false
            )
            ->setIsTransactionClosed(
                true
            )
            ->resetTransactionAdditionalInfo(
            );

        $this->getModuleHelper()->setPaymentTransactionAdditionalInfo(
            $payment,
            $responseObject
        );

        $payment->save();

        return $responseObject;
    }

    /**
     * The function is used to capture the payment for an order
     * 
     * @param \Magento\Payment\Model\InfoInterface payment The payment object
     * @param amount The amount to capture.
     * @param authTransaction The authorization transaction object.
     * 
     * @return The method is returning the class instance.
     */
    protected function doCapture(\Magento\Payment\Model\InfoInterface $payment, $amount, $authTransaction)
    {
        $order = $payment->getOrder();
        $helper = $this->getModuleHelper();

        $data = [
            'reference_id'   =>
                $authTransaction->getTxnId(),
            'currency'       =>
                $order->getBaseCurrencyCode(),
            'amount'         =>
                $amount
        ];

        $responseObject = $this->processReferenceTransaction(
            $helper::CAPTURE,
            $payment,
            $data
        );

        if ($responseObject->isSuccess()) {
            $this->getMessageManager()->addSuccess($responseObject->getMessage());
        } else {
            $this->getModuleHelper()->throwWebApiException(
                $responseObject->getMessage()
            );
        }

        unset($data);

        return $this;
    }
    
    /**
     * It takes the transaction ID of the capture transaction and sends it to the gateway to be
     * refunded
     * 
     * @param \Magento\Payment\Model\InfoInterface payment The payment object
     * @param amount The amount to refund.
     * @param captureTransaction The transaction that you want to refund.
     * 
     * @return The method returns the object of the class.
     */
    public function doRefund(\Magento\Payment\Model\InfoInterface $payment, $amount, $captureTransaction)
    {
        $order = $payment->getOrder();
        $helper = $this->getModuleHelper();
        if (!$this->getModuleHelper()->canRefundTransaction($captureTransaction)) {
            $errorMessage = __('Order cannot be refunded online.');

            $this->getMessageManager()->addError($errorMessage);
            $this->getModuleHelper()->throwWebApiException($errorMessage);
        }
        $data = [
            'reference_id'   =>
                $captureTransaction->getTxnId(),
            'currency'       =>
                $order->getBaseCurrencyCode(),
            'amount'         =>
                $amount,
            'reason'         => __('Merchant refund')
        ];

        $responseObject = $this->processReferenceTransaction(
            $helper::REFUND,
            $payment,
            $data
        );

        if ($responseObject->isSuccess()) {
            $this->getMessageManager()->addSuccess($responseObject->getMessage());
        } else {
            $this->getMessageManager()->addError($responseObject->getMessage());
            $this->getModuleHelper()->throwWebApiException(
                $responseObject->getMessage()
            );
        }

        unset($data);

        return $this;
    }

    /**
     * This function is used to void a transaction
     * 
     * @param \Magento\Payment\Model\InfoInterface payment The payment object
     * @param authTransaction The authorization transaction object.
     * @param referenceTransaction The transaction that you want to void.
     * 
     * @return The method returns the payment object.
     */
    public function doVoid(
        \Magento\Payment\Model\InfoInterface $payment, 
        $authTransaction, 
        $referenceTransaction
    ) {

        $order = $payment->getOrder();
        $helper = $this->getModuleHelper();

        $data = [
            'reference_id'   =>
                $referenceTransaction->getTxnId(),
            'currency'       =>
                $order->getBaseCurrencyCode(),
            'amount'         =>
                $amount
        ];

        $responseObject = $this->processReferenceTransaction(
            $helper::VOID,
            $payment,
            $data
        );

        if ($responseObject->isSuccess()) {
            $this->getMessageManager()->addSuccess($responseObject->getMessage());
        } else {
            $this->getMessageManager()->addError($responseObject->getMessage());
            $this->getModuleHelper()->throwWebApiException(
                $responseObject->getMessage()
            );
        }

        unset($data);

        return $this;
    }
}
