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
class Allopass_Hipay_Model_Method_CcXtimes extends Allopass_Hipay_Model_Method_Cc
{
    use Allopass_Hipay_Model_Method_SplitPaymentTrait;

    protected $_canUseInternal = false;

    protected $_code = 'hipay_ccxtimes';

    /**
     * Check whether payment method can be used
     *
     * @param Mage_Sales_Model_Quote|null $quote
     *
     * @return bool
     */
    public function isAvailable($quote = null)
    {
        if ($quote !== null) {
            $checkoutMethod = $quote->getCheckoutMethod();

            if ($checkoutMethod == Mage_Checkout_Model_Type_Onepage::METHOD_GUEST) {
                return false;
            }
        }

        return parent::isAvailable($quote);
    }
}
