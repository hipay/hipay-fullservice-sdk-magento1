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
 * Allopass Hipay Activate 3DS
 *
 * @author      HiPay <support.tpp@hipay.com>
 * @copyright   Copyright (c) 2018 - HiPay
 * @license     https://github.com/hipay/hipay-fullservice-sdk-magento1/blob/master/LICENSE.md
 * @link    https://github.com/hipay/hipay-fullservice-sdk-magento1
 */
class Allopass_Hipay_Model_Source_3ds
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => 1, 'label' => Mage::helper('hipay')->__('Try to enable for all transactions.')),
            array('value' => 2, 'label' => Mage::helper('hipay')->__('Try to enable for configured 3ds rules')),
            array('value' => 3, 'label' => Mage::helper('hipay')->__('Force for configured 3ds rules')),
            array('value' => 4, 'label' => Mage::helper('hipay')->__('Force for all transactions.')),
            array('value' => 0, 'label' => Mage::helper('hipay')->__('Disabled')),

        );
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return array(
            0 => Mage::helper('hipay')->__('Disabled'),
            1 => Mage::helper('hipay')->__('Enable for all transactions'),
            2 => Mage::helper('hipay')->__('Enable for configured 3ds rules'),
            3 => Mage::helper('hipay')->__('Force for configured 3ds rules'),
            4 => Mage::helper('hipay')->__('Force for all transactions.'),
        );
    }

}
