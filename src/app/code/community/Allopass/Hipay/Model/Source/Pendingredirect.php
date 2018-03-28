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
 * Allopass Hipay redirect url for pending
 *
 * @author      HiPay <support.tpp@hipay.com>
 * @copyright   Copyright (c) 2018 - HiPay
 * @license     https://github.com/hipay/hipay-fullservice-sdk-magento1/blob/master/LICENSE.md
 * @link    https://github.com/hipay/hipay-fullservice-sdk-magento1
 */
class Allopass_Hipay_Model_Source_Pendingredirect
{
    public function toOptionArray()
    {

        return array(
            array('value' => 'hipay/checkout/pending', 'label' => Mage::helper('hipay')->__('Pending page')),
            array('value' => 'checkout/onepage/success', 'label' => Mage::helper('hipay')->__('Success page')),
            array('value' => 'checkout/onepage/failure', 'label' => Mage::helper('hipay')->__('Failure page')),
        );
    }
}
