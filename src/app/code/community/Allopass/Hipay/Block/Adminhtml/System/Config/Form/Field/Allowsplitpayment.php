<?php

class Allopass_Hipay_Block_Adminhtml_System_Config_Form_Field_Allowsplitpayment extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    
    /**
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $javaScript = "
            <script type=\"text/javascript\">
                Event.observe('{$element->getId()}', 'change', function(){
                    split_payment=$('{$element->getId()}').value;
                    $('{$this->_getSplitPaymentElementId($element)}').disabled = (!split_payment || split_payment!=1);
                });
            </script>";
        
        $element->setData('after_element_html', $javaScript.$element->getAfterElementHtml());
        
        $this->toggleDisabled($element);
    
        return parent::_getElementHtml($element);
    }

    public function toggleDisabled($element)
    {
        if (!$element->getValue() || $element->getValue()!=1) {
            $element->getForm()->getElement($this->_getSplitPaymentElementId($element))->setDisabled('disabled');
        }
        return parent::getHtml();
    }

    protected function _getSplitPaymentElementId($element)
    {
        return substr($element->getId(), 0, strrpos($element->getId(), 'allow_split_payment')) . 'split_payment_profile';
    }
}
