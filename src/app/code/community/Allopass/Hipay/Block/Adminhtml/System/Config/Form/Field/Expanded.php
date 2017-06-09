<?php
/**
 * Class Allopass_Hipay_Block_Adminhtml_System_Config_Form_Field_Expanded
 */
class Allopass_Hipay_Block_Adminhtml_System_Config_Form_Field_Expanded
    extends Mage_Adminhtml_Block_System_Config_Form_Fieldset
{
    /**
     * Return collapse state
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return bool
     */
    protected function _getCollapseState($element)
    {
        $extra = Mage::getSingleton('admin/session')->getUser()->getExtra();
        if (isset($extra['configState'][$element->getId()])) {
            return $extra['configState'][$element->getId()];
        }

        return true;
    }
}
