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
class Allopass_Hipay_Model_Method_Bnpp extends Allopass_Hipay_Model_Method_AbstractOrderApi
{

    /**
     * Validate payment method information object
     *
     * @throws Mage_Core_Exception
     */
    public function validate()
    {
        parent::validate();
        $paymentInfo = $this->getInfoInstance();

        if ($paymentInfo instanceof Mage_Sales_Model_Order_Payment) {
            $phone = $paymentInfo->getOrder()->getBillingAddress()->getTelephone();
        } else {
            $phone = $paymentInfo->getQuote()->getBillingAddress()->getTelephone();
        }

        if (!preg_match('"(0|\\+33|0033)[1-9][0-9]{8}"', $phone)) {
            Mage::throwException(Mage::helper('payment')->__('Please check the phone number entered.'));
        }
    }
}
