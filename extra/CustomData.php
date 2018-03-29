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
 *       This custom class must be placed in the folder /AlloPass/Hipay/Helper
 *       You have to personalize  the method getCustomData and return an json of your choice.
 *
 * @author      HiPay <support.tpp@hipay.com>
 * @copyright   Copyright (c) 2018 - HiPay
 * @license     https://github.com/hipay/hipay-fullservice-sdk-magento1/blob/master/LICENSE.md
 * @link    https://github.com/hipay/hipay-fullservice-sdk-magento1
 */
class Allopass_Hipay_Helper_CustomData extends Mage_Core_Helper_Abstract
{

    /**
     * Return yours customs datas in a json for gateway transaction request
     *
     * @param $payment
     * @param $amount
     * @return array
     */
    public function getCustomData($payment, $amount)
    {
        $customData = array();

        // An example of adding custom data
        if ($payment) {
            $customData['my_field_custom_1'] = $payment->getOrder()->getBaseCurrencyCode();
        }

        return $customData;
    }
}
