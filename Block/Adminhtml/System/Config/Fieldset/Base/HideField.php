<?php
namespace Eligmaltd\GoCryptoPay\Block\Adminhtml\System\Config\Fieldset\Base;
use Eligmaltd\GoCryptoPay\Helper\Data;
use Magento\Framework\Data\Form\Element\AbstractElement;

class HideField extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * The constructor function is called when the class is instantiated. It is used to set up the
     * class
     * 
     * @param \Magento\Backend\Block\Template\Context context This is the context of the block.
     * @param Data dataHelper This is the helper class that we created in the previous step.
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
     * The function `_prepareToRender()` is called when the form is loaded. It adds two columns to the
     * form, one with the label "Cargo Type" and the other with the label "Attribute Set". The
     * `_addAfter` property is set to false, which means that the "Add" button will be placed before
     * the table. The `_addButtonLabel` property is set to "Add", which means that the "Add" button
     * will have the label "Add"
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
     * If the client_id and client_secret are not null, then display the row. Otherwise, hide it
     * 
     * @param \Magento\Framework\Data\Form\Element\AbstractElement element The element that is being
     * rendered.
     * @param html The html of the element
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
            return '<tr id="row_' . $element->getHtmlId().'">' . $html . '</tr>';
        }
        return '<tr id="row_' . $element->getHtmlId().'"' . $style .'>' . $html . '</tr>';
    }

    /**
     * > This function disables the field in the admin panel
     * 
     * @param AbstractElement element The element that you want to disable.
     * 
     * @return The HTML for the element.
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $element->setDisabled('disabled');
        return $element->getElementHtml();
    }
}
