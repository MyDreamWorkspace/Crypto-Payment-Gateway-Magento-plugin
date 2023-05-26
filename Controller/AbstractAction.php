<?php

namespace Eligmaltd\GoCryptoPay\Controller;

abstract class AbstractAction extends \Magento\Framework\App\Action\Action
{
    /* @var \Magento\Framework\App\Action\Context $_context*/
    private $_context;

    /* @var \Psr\Log\LoggerInterface $_logger*/
    private $_logger;

    /**
     * The constructor is a function that is called when the class is instantiated. It is used to set
     * up the class
     * 
     * @param \Magento\Framework\App\Action\Context context This is the context of the action.
     * @param \Psr\Log\LoggerInterface logger This is the logger interface that we will use to log
     * messages.
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Psr\Log\LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->_context = $context;
        $this->_logger = $logger;
    }

    /**
     * > This function returns the context of the current page
     * 
     * @return The context of the current request.
     */
    protected function getContext()
    {
        return $this->_context;
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
     * > This function returns the message manager
     * 
     * @return The message manager.
     */
    protected function getMessageManager()
    {
        return $this->getContext()->getMessageManager();
    }

    /**
     * > This function returns the logger
     * 
     * @return The logger object.
     */
    protected function getLogger()
    {
        return $this->_logger;
    }

    /**
     * > This function checks if a POST request exists
     * 
     * @param key The key of the post request you want to check.
     */
    protected function isPostRequestExists($key)
    {
        $post = $this->getPostRequest();

        return isset($post[$key]);
    }

    /**
     * It returns the value of a POST request, or null if the key doesn't exist
     * 
     * @param key The key of the post value you want to retrieve.
     * 
     * @return The post request.
     */
    protected function getPostRequest($key = null)
    {
        $post = $this->getRequest()->getPostValue();

        if (isset($key) && isset($post[$key])) {
            return $post[$key];
        } elseif (isset($key)) {
            return null;
        } else {
            return $post;
        }
    }
}
