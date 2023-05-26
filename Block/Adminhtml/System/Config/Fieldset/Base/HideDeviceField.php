<?php
namespace Eligmaltd\GoCryptoPay\Block\Adminhtml\System\Config\Fieldset\Base;

use Eligmaltd\GoCryptoPay\Helper\Data;

class HideDeviceField extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * The constructor function is called when the class is instantiated. It takes in the context of
     * the page, the data helper, and any data that is passed to it. It then sets the config variable
     * to the scope config
     * 
     * @param \Magento\Backend\Block\Template\Context context This is the context of the block.
     * @param Data dataHelper This is the helper class that we created earlier.
     * @param array data This is the data that is passed to the block.
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        Data $dataHelper,
        array $data = []
    ) {
        $this->config = $dataHelper->getScopeConfig();
        parent::__construct($context, $data);
    }

    /**
     * The function adds two columns to the grid, one with the label "Cargo Type" and the other with
     * the label "Attribute Set"
     */
    protected function _prepareToRender()
    {
        $this->addColumn('tab1', ['label' => __('Cargo Type'),  'class' => 'required-entry']);
        $this->addColumn('tab2', ['label' => __('Attribute Set'),  'class' => 'required-entry']);
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add');
    }

    /**
     * It renders the label, value, inherit checkbox, and hint for the element
     * 
     * @param \Magento\Framework\Data\Form\Element\AbstractElement element The element object
     * 
     * @return The HTML for the element.
     */
    public function render(
        \Magento\Framework\Data\Form\Element\AbstractElement $element
    ) {
        $isCheckboxRequired = $this->_isInheritCheckboxRequired($element);

        if ($element->getInherit() == 1 && $isCheckboxRequired) {
            $element->setDisabled(true);
        }

        $html = '<td class="label"><label for="' .
            $element->getHtmlId() . '"><span' .
            $this->_renderScopeLabel($element) . '>' .
            $element->getLabel() .
            '</span></label></td>';
        $html .= $this->_renderValue($element);

        if ($isCheckboxRequired) {
            $html .= $this->_renderInheritCheckbox($element);
        }

        $html .= $this->_renderHint($element);

        return $this->_decorateRowHtml($element, $html);
    }

    /**
     * If the client_id and client_secret are not set, then the row will be displayed. If they are set,
     * then the row will be hidden
     * 
     * @param \Magento\Framework\Data\Form\Element\AbstractElement element The element that is being
     * rendered.
     * @param html The HTML of the element
     * 
     * @return the html for the row.
     */
    protected function _decorateRowHtml(
        \Magento\Framework\Data\Form\Element\AbstractElement $element, $html
    ) {
        $style = 'style="display: none"';
        if ($this->config->getValue('payment/gocrypto_pay/client_id', 
        \Magento\Store\Model\ScopeInterface::SCOPE_STORE) != null && 
        $this->config->getValue('payment/gocrypto_pay/client_secret',  
        \Magento\Store\Model\ScopeInterface::SCOPE_STORE) != null) {
            return '<tr id="row_' . $element->getHtmlId().'" ' . $style .'">' . $html . '</tr>';
        }
        return '<tr id="row_' . $element->getHtmlId().'">' . $html . '</tr>';
    }
}
