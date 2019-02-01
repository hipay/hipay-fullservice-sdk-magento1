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
class Allopass_Hipay_Model_Source_Product_Facilipay4x
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => '4xcb', 'label' => Mage::helper('hipay')->__('With fees')),
            array('value' => '4xcb-no-fees', 'label' => Mage::helper('hipay')->__('Without fees')),
        );
    }
}
