<?php

/**
 * HiPay Fullservice SDK Magento 1
 *
 * 2018 HiPay
 *
 * NOTICE OF LICENSE
 *
 * @author    HiPay <support.tpp@hipay.com>
 * @copyright 2018 HiPay
 * @license   https://github.com/hipay/hipay-fullservice-sdk-magento1/blob/master/LICENSE.md
 */

/**
 *
 * Class Allopass_Hipay_Block_Adminhtml_System_Config_Form_Field_Notice
 *
 * @author      HiPay <support.tpp@hipay.com>
 * @copyright   Copyright (c) 2018 - HiPay
 * @license     https://github.com/hipay/hipay-fullservice-sdk-magento1/blob/master/LICENSE.md
 * @link    https://github.com/hipay/hipay-fullservice-sdk-magento1
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
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
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
        $commonWarning = Mage::helper('hipay')->__(
            'All mappings are not filled in, please check the information you entered.
                If the mappings are not filled in, then Oney transactions will be denied.'
        );

        // Notice for block Mapping shipping method
        if (preg_match('/mapping_shipping_method/', $element->getId())) {
            $nbMappingMissing = Mage::helper('hipay')->checkMappingShippingMethod();
            if ($nbMappingMissing > 0) {
                $notices[] = $commonWarning . '<div class="nb-mapping-missing">
                ' . $nbMappingMissing . ' '
                    . Mage::helper('hipay')->__('mappings delivery method are actually missing.') . '</div>';
            }
        }

        // Notice for block Category
        if (preg_match('/mapping_category/', $element->getId())) {
            $nbMappingMissing = Mage::helper('hipay')->checkMappingCategoryMethod();
            if ($nbMappingMissing > 0) {
                $notices[] = $commonWarning . '<div class="nb-mapping-missing">
                ' . $nbMappingMissing . ' '
                    . Mage::helper('hipay')->__('mappings categories are actually missing.') . '</div>';
            }
        }

        // Notice for Hashing
        if (preg_match('/hashing/', $element->getId())) {
            $notices[] = Mage::helper('hipay')->__(
                'If the hash configuration is different than the one set in your Hipay back office, then the notifications will not work. Check that both values match.'
            );
        }

        $element->setNoticesHipay($notices);
        $this->setElement($element);
        return $this->_toHtml();
    }
}
