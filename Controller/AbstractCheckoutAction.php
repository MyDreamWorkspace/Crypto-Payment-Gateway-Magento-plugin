<?php
namespace Eligmaltd\GoCryptoPay\Controller;

use Eligmaltd\GoCryptoPay\Helper\Data;

abstract class AbstractCheckoutAction extends \Eligmaltd\GoCryptoPay\Controller\AbstractAction
{
    /* @var \Magento\Checkout\Model\Session $_checkoutSession*/
    private $_checkoutSession;

    /* @var Data $_dataHelper*/
    private $_dataHelper;

    /* @var \Magento\Sales\Model\OrderFactory $_orderFactory*/
    private $_orderFactory;

    /**
     * This function is called when the controller is instantiated. It takes in the context, logger,
     * checkout session, order factory, and data helper. It then calls the parent constructor and sets
     * the checkout session, order factory, and data helper to the class variables
     * 
     * @param \Magento\Framework\App\Action\Context context This is the context of the controller.
     * @param \Psr\Log\LoggerInterface logger This is the logger interface.
     * @param \Magento\Checkout\Model\Session checkoutSession This is the session object that contains
     * the order information.
     * @param \Magento\Sales\Model\OrderFactory orderFactory This is the Magento Order Factory.
     * @param Data dataHelper This is the helper class that we created in the previous step.
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        Data $dataHelper
    ) {
        parent::__construct($context, $logger);
        $this->_checkoutSession = $checkoutSession;
        $this->_orderFactory = $orderFactory;
        $this->_dataHelper = $dataHelper;
    }

    protected function getCheckoutSession()
    {
        return $this->_checkoutSession;
    }

    /**
     * It returns the data helper.
     * 
     * @return The data helper.
     */
    protected function getDataHelper()
    {
        return $this->_dataHelper;
    }

    /**
     * It returns the order factory.
     * 
     * @return The order factory.
     */
    protected function getOrderFactory()
    {
        return $this->_orderFactory;
    }

    /**
     * It gets the order ID from the checkout session, loads the order by the order ID, and returns the
     * order
     * 
     * @return The order object.
     */
    protected function getOrder()
    {
        $orderId = $this->getCheckoutSession()->getLastRealOrderId();

        if (!isset($orderId)) {
            return null;
        }

        $order = $this->getOrderFactory()->create()->loadByIncrementId(
            $orderId
        );

        if (!$order->getId()) {
            return null;
        }

        return $order;
    }

    /**
     * It redirects the user to the checkout page, and then scrolls down to the payment section
     */
    protected function redirectToCheckoutFragmentPayment()
    {
        $this->_redirect('checkout', ['_fragment' => 'payment']);
    }

    /**
     * It redirects the user to the success page after a successful checkout
     */
    protected function redirectToCheckoutOnePageSuccess()
    {
        $this->_redirect('checkout/onepage/success');
    }

    /**
     * It redirects the user to the cart page
     */
    protected function redirectToCheckoutCart()
    {
        $this->_redirect('checkout/cart');
    }
}
