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
class Allopass_Hipay_Block_Checkout_Externaljs extends Mage_Core_Block_Template
{

    /**
     * @return mixed
     * @throws Mage_Core_Model_Store_Exception
     */
    public function fingerprintEnabled()
    {
        return $this->getConfig()->isFingerprintEnabled(Mage::app()->getStore());
    }

    /**
     * @return mixed
     * @throws Mage_Core_Model_Store_Exception
     */
    public function sdkJsNeeded()
    {
        return $this->getConfig()->isPaymentMethodActivated('hipay_cc', Mage::app()->getStore())
            || $this->getConfig()->isPaymentMethodActivated('hipay_ccxtimes', Mage::app()->getStore())
            || $this->getConfig()->isPaymentMethodActivated('hipay_hostedfieldsxtimes', Mage::app()->getStore())
            || $this->getConfig()->isPaymentMethodActivated('hipay_hostedfields', Mage::app()->getStore());
    }

    /**
     * @return mixed
     * @throws Mage_Core_Model_Store_Exception
     */
    public function getFingerPrintJs()
    {
        return $this->getConfig()->getFingerPrintJsUrl(Mage::app()->getStore());
    }

    /**
     * @return mixed
     * @throws Mage_Core_Model_Store_Exception
     */
    public function getSdkJs()
    {
        return $this->getConfig()->getSdkJsUrl(Mage::app()->getStore());
    }

    /**
     *
     * @return Allopass_Hipay_Model_Config
     */
    protected function getConfig()
    {
        return Mage::getSingleton('hipay/config');
    }
}
