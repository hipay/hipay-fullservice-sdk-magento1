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
class Allopass_Hipay_Model_System_Config_Backend_CcValidCredentials extends Mage_Core_Model_Config_Data
{

    /**
     * @throws Mage_Core_Exception
     */
    protected function _beforeSave()
    {
        $store = null;
        if ($this->getStoreCode()) {
            $store = $this->getStoreCode();
        } elseif ($this->getWebsiteCode()) {
            $store = $this->getWebsiteCode();
        }

        if ((bool)$this->getValue() && $this->getHiPayConfig()->arePublicCredentialsEmpty($store)) {
            Mage::throwException(
                Mage::helper('adminhtml')->__(
                    'In order to activate HiPay Enterprise API credit card or HiPay Enterprise API credit card'.
                    ' Split Payment, you have to add a valid \'Api TokenJS Password/Public Key\' on the HiPay'.
                    ' Entreprise configuration'
                )
            );
        }
    }

    /**
     * @return Mage_Core_Model_Abstract
     */
    private function getHiPayConfig()
    {
        return Mage::getSingleton('hipay/config');
    }
}
