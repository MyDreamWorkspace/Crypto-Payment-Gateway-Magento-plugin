<?php
namespace Eligmaltd\GoCryptoPay\Block\Adminhtml\System\Config\Fieldset\Base;

abstract class Payment extends \Magento\Config\Block\System\Config\Form\Fieldset
{
    /* @var \Magento\Config\Model\Config $_backendConfig*/
    protected $_backendConfig;

    /**
     * > This function is a constructor for the class. It is called when an object is instantiated from
     * the class
     * 
     * @param \Magento\Backend\Block\Context context This is the context of the block.
     * @param \Magento\Backend\Model\Auth\Session authSession This is the session object for the admin
     * user.
     * @param \Magento\Framework\View\Helper\Js jsHelper This is a helper class that is used to
     * generate JavaScript code.
     * @param \Magento\Config\Model\Config backendConfig This is the model that will be used to save
     * the configuration.
     * @param array data This is an array of data that will be passed to the block.
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Framework\View\Helper\Js $jsHelper,
        \Magento\Config\Model\Config $backendConfig,
        array $data = []
    ) {
        $this->_backendConfig = $backendConfig;
        parent::__construct($context, $authSession, $jsHelper, $data);
    }
}
