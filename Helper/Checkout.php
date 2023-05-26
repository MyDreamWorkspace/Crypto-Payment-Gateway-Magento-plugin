<?php
namespace Eligmaltd\GoCryptoPay\Helper;

use Magento\Sales\Model\Order;

class Checkout
{
    /* @var \Magento\Checkout\Model\Session $_checkoutSession*/
    protected $_checkoutSession;

    /**
     * The constructor function is used to create an instance of the class
     * 
     * @param \Magento\Checkout\Model\Session checkoutSession This is the checkout session object.
     */
    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        $this->_checkoutSession = $checkoutSession;
    }

    /**
     * It returns the checkout session
     * 
     * @return The checkout session object.
     */
    protected function getCheckoutSession()
    {
        return $this->_checkoutSession;
    }

    /**
     * It cancels the current order
     * 
     * @param comment The reason for cancelling the order.
     * 
     * @return The order object is being returned.
     */
    public function cancelCurrentOrder($comment)
    {
        $order = $this->getCheckoutSession()->getLastRealOrder();
        if ($order->getId() && $order->getState() != Order::STATE_CANCELED) {
            $order->registerCancellation($comment)->save();
            return true;
        }
        return false;
    }

    /**
     * It restores the quote
     * 
     * @return The quote object.
     */
    public function restoreQuote()
    {
        return $this->getCheckoutSession()->restoreQuote();
    }
}
