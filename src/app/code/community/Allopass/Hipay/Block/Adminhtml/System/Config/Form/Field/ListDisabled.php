<?php

class Allopass_Hipay_Block_Adminhtml_System_Config_Form_Field_ListDisabled extends Mage_Adminhtml_Block_System_Config_Form_Field {

    /**
     *  Set element Disabled
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    protected function _getElementHtml($element) {
        $element->setDisabled('disabled');
        return parent::_getElementHtml($element);
    }

    /**
     *  Unset Scope
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }

}
