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
class Allopass_Hipay_Model_Source_Order_HipayStatusValidate
{
    public function toOptionArray()
    {
        $options = array();

        $options[] = array(
            'value' => 117,
            'label' => Mage::helper('hipay')->__('Capture Requested')
        );

        $options[] = array(
            'value' => 118,
            'label' => Mage::helper('hipay')->__('Capture')
        );

        return $options;
    }
}
