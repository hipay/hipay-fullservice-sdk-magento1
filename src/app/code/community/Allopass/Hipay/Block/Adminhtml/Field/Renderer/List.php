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
 *
 * @author      HiPay <support.tpp@hipay.com>
 * @copyright   Copyright (c) 2018 - HiPay
 * @license     https://github.com/hipay/hipay-fullservice-sdk-magento1/blob/master/LICENSE.md
 * @link    https://github.com/hipay/hipay-fullservice-sdk-magento1
 */
class Allopass_Hipay_Block_Adminhtml_Field_Renderer_List extends Mage_Core_Block_Html_Select
{
    /**
     * Original data source
     *
     * @var array
     */
    protected $_listOptions;

    /**
     *  Return block List with prepared select
     *
     * @return string
     */
    public function _toHtml()
    {
        $this->setName($this->inputName);

        switch ($this->column_name) {
            case "hipay_category":
                $defaultValue = Mage::helper('hipay')->__('- Please select one category - ');
                break;
            case "hipay_delivery_method":
                $defaultValue = Mage::helper('hipay')->__('- Please select one delivery method - ');
                break;
        }

        $this->addOption('', $defaultValue);
        foreach ($this->_listOptions as $key => $value) {
            $this->addOption($key, $value);
        }
        
        return parent::_toHtml();
    }

    /**
     * Init initial list of options
     *
     * @param $options
     */
    public function setListOptions($options)
    {
        $this->_listOptions = $options;
    }
}
