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
class Allopass_Hipay_Block_Checkout_Cart_Totals extends Mage_Checkout_Block_Cart_Totals
{
    /**
     * Check if we have display grand total in base currency
     *
     * @return bool
     */
    public function needDisplayBaseGrandtotal()
    {
        $quote = $this->getQuote();
        $useOrderCurrency = Mage::getStoreConfig('hipay/hipay_api/currency_transaction', Mage::app()->getStore());

        if (!$useOrderCurrency) {
            if ($quote->getBaseCurrencyCode() != $quote->getQuoteCurrencyCode()) {
                return true;
            }
        }

        return false;
    }

}
