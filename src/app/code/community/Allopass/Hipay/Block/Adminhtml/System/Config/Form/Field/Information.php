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
class Allopass_Hipay_Block_Adminhtml_System_Config_Form_Field_Information extends Mage_Adminhtml_Block_System_Config_Form_Field
{

    /**
     * Check if columns are defined, set template
     *
     */
    public function __construct()
    {
        parent::__construct();

        if (!$this->getTemplate()) {
            $this->setTemplate('hipay/system/config/form/field/information.phtml');
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
        $element->setInformationsHipay($element->getData('tooltip'));
        $this->setElement($element);
        return $this->_toHtml();
    }
}
