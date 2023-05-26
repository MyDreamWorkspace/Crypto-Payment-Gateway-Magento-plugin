<?php
namespace Eligmaltd\GoCryptoPay\Controller;

use Eligmaltd\GoCryptoPay\Helper\Data;

abstract class AbstractCheckoutRedirectAction extends \Eligmaltd\GoCryptoPay\Controller\AbstractCheckoutAction
{
    /* @var \Eligmaltd\GoCryptoPay\Helper\Checkout $_checkoutHelper*/
    private $_checkoutHelper;
  
    /**
     * The function is a constructor for the class. It is called when the class is instantiated. It
     * takes in the following parameters:
     * 
     * * \Magento\Framework\App\Action\Context 
     * * \Psr\Log\LoggerInterface 
     * * \Magento\Checkout\Model\Session 
     * * \Magento\Sales\Model\OrderFactory 
     * * \Eligmaltd\GoCryptoPay\Helper\Checkout 
     * * Data 
     * 
     * The function then calls the parent constructor, passing in the parameters
     * 
     * @param \Magento\Framework\App\Action\Context context This is the context of the action.
     * @param \Psr\Log\LoggerInterface logger This is the logger interface.
     * @param \Magento\Checkout\Model\Session checkoutSession This is the session object that contains
     * the order information.
     * @param \Magento\Sales\Model\OrderFactory orderFactory This is the Magento Order Factory.
     * @param \Eligmaltd\GoCryptoPay\Helper\Checkout checkoutHelper This is the helper class that
     * contains the logic for the checkout process.
     * @param Data dataHelper This is the helper class that contains the logic for the GoCryptoPay
     * payment gateway.
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Eligmaltd\GoCryptoPay\Helper\Checkout $checkoutHelper,
        Data $dataHelper
    ) {
        parent::__construct($context, $logger, $checkoutSession, $orderFactory, $dataHelper);
        $this->_checkoutHelper = $checkoutHelper;
    }

    /**
     * It returns the checkout helper.
     * 
     * @return The checkout helper.
     */
    protected function getCheckoutHelper()
    {
        return $this->_checkoutHelper;
    }

    /**
     * If the last order ID is set, add a success message to the message manager and redirect to the
     * one page checkout success page
     */
    protected function executeSuccessAction()
    {
        if ($this->getCheckoutSession()->getLastRealOrderId()) {
            $this->getMessageManager()->addSuccess(__("Your payment is complete"));
            $this->redirectToCheckoutOnePageSuccess();
        }
    }

    /**
     * It cancels the current order, restores the quote, and redirects to the checkout cart
     */
    protected function executeCancelAction()
    {
        $this->getCheckoutHelper()->cancelCurrentOrder('');
        $this->getCheckoutHelper()->restoreQuote();
        $this->redirectToCheckoutCart();
    }
    
    /**
     * It returns the value of the action parameter in the request object
     * 
     * @return The action parameter from the request object.
     */
    protected function getReturnAction()
    {
        return $this->getRequest()->getParam('action');
    }
}
