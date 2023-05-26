<?php
namespace Eligmaltd\GoCryptoPay\Controller\Order;

use Eligmaltd\GoCryptoPay\Lib\GoCryptoPay;
use Eligmaltd\GoCryptoPay\Helper\Data;
use Magento\Framework\Controller\ResultFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Psr\Log\LoggerInterface as Logger;

class Status extends \Eligmaltd\GoCryptoPay\Controller\AbstractCheckoutAction
{
    /* @var \Psr\Log\LoggerInterface $logger*/
    protected $logger;

    /**
     * This function is called when the class is instantiated. It takes in the context, logger,
     * checkout session, order factory, and data helper. It then calls the parent constructor and sets
     * the logger
     * 
     * @param \Magento\Framework\App\Action\Context context This is the context of the current request.
     * @param \Psr\Log\LoggerInterface logger This is the logger object that will be used to log
     * messages.
     * @param \Magento\Checkout\Model\Session checkoutSession This is the session object that contains
     * the order information.
     * @param \Magento\Sales\Model\OrderFactory orderFactory This is the order factory that will be
     * used to load the order.
     * @param Data dataHelper This is the helper class that we created in the previous step.
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        Data $dataHelper
    ) {
        parent::__construct($context, $logger, $checkoutSession, $orderFactory, $dataHelper);
        $this->logger = $logger;
    }

    /**
     * It decrypts the order ID, checks the transaction status, and redirects the user to the
     * appropriate page
     * 
     * @return The return is a JSON object with the following structure:
     * ```
     * {
     *     "id": "5e8f8f8f8f8f8f8f8f8f8f8f",
     *     "status": "SUCCESS",
     *     "amount": "0.01",
     *     "currency": "EUR",
     *     "created
     */
    public function execute()
    {
        $config = $this->getDataHelper()->getScopeConfig();
        $host = $config->getValue('payment/gocrypto_pay/host', 
        \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $isSand = $config->getValue('payment/gocrypto_pay/is_sandbox', 
        \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $clientId = $config->getValue('payment/gocrypto_pay/client_id', 
        \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $clientSecret = $config->getValue('payment/gocrypto_pay/client_secret', 
        \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        $encryptedId = $this->getRequest()->getParam('order_id');
        $decryption_iv = '1234567891011121';
        $ciphering = "AES-128-CTR";
        $options = 0;
        $orderId = openssl_decrypt ($encryptedId, $ciphering, $clientId, $options, $decryption_iv);

        $transactionId = $this->getRequest()->getParam('transaction_id');
        $orderRepository = \Magento\Framework\App\ObjectManager::getInstance()->get(OrderRepositoryInterface::class);
        $order = $orderRepository->get($orderId);

        if ($order) {
            $gocryptoPay = new GoCryptoPay($isSand);
            $gocryptoPay->config($host);

            $gocryptoPay->setCredentials($clientId, $clientSecret);
            if ($gocryptoPay->auth()) {
                if ($transactionId) {
                    $transactionStatus = $gocryptoPay->checkTransactionStatus($transactionId);
                    if ($transactionStatus == 'IN_PROGRESS') {
                        $this->_redirect('checkout/onepage/pending');
                        return;
                    } elseif ($transactionStatus == 'SUCCESS') {

                        $order->setState('processing')
                            ->setStatus('processing')
                            ->addStatusHistoryComment(__('Payment approved for Transaction ID: "%1".', $transactionId));

                        $orderRepository->save($order);
                        $this->_redirect('checkout/onepage/success');
                        return;
                    } else {
                        $order->setState('canceled')
                            ->setStatus('canceled')
                            ->addStatusHistoryComment(__('Payment declined for Transaction ID: "%1".', $transactionId));

                        $orderRepository->save($order);
                        $this->_redirect('checkout/cart');
                        return;
                    }
                } else {
                    $order->setState('canceled')
                        ->setStatus('canceled')
                        ->addStatusHistoryComment(__('Transaction failed.'));
                    $orderRepository->save($order);
                    $this->_redirect('checkout/cart');
                    return;
                }
            } else {
                $order->setState('canceled')
                    ->setStatus('canceled')
                    ->addStatusHistoryComment(__('Payment auth failed.'));
                $orderRepository->save($order);
                $this->_redirect('checkout/cart');
                return;
            }
        }
    }
}
