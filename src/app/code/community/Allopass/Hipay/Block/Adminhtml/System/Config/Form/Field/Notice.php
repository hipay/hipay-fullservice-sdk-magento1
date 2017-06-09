<?php

/**
 * Class Allopass_Hipay_Block_Adminhtml_System_Config_Form_Field_Notice
 */
class Allopass_Hipay_Block_Adminhtml_System_Config_Form_Field_Notice extends Mage_Adminhtml_Block_System_Config_Form_Field
{

    /**
     * Set template
     *
     */
    public function __construct()
    {
        parent::__construct();
        if (!$this->getTemplate()) {
            $this->setTemplate('hipay/system/config/form/field/notice.phtml');
        }
    }

    /**
     * Custom field
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $notices = array();
        $commonWarning = Mage::helper('hipay')->__('All mappings are not filled in, please check the information you entered.
                If the mappings are not filled in, then Oney transactions will be denied.');

        // Notice for block Mapping shipping method
        if (preg_match('/mapping_shipping_method/', $element->getId())){
            $nbMappingMissing = Mage::helper('hipay')->checkMappingShippingMethod();
            if ($nbMappingMissing > 0) {
                $notices[] = $commonWarning . '<div class="nb-mapping-missing">
                ' . $nbMappingMissing . ' '. Mage::helper('hipay')->__('mappings delivery method are actually missing.') .'</div>';

            }
        }

        // Notice for block Category
        if (preg_match('/mapping_category/', $element->getId())){
            $nbMappingMissing = Mage::helper('hipay')->checkMappingCategoryMethod();
            if ($nbMappingMissing > 0) {
                $notices[] = $commonWarning . '<div class="nb-mapping-missing">
                ' . $nbMappingMissing . ' '. Mage::helper('hipay')->__('mappings categories are actually missing.') . '</div>';
            }
        }

        $element->setNoticesHipay($notices);
        $this->setElement($element);
        return $this->_toHtml();
    }
}