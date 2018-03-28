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
 * Allopass Hipay Payments Profiles
 *
 * @author      HiPay <support.tpp@hipay.com>
 * @copyright   Copyright (c) 2018 - HiPay
 * @license     https://github.com/hipay/hipay-fullservice-sdk-magento1/blob/master/LICENSE.md
 * @link    https://github.com/hipay/hipay-fullservice-sdk-magento1
 */
class Allopass_Hipay_Model_Source_PaymentProfile extends Varien_Object
{


    protected $_collection = null;

    protected function _getCollection()
    {
        if (is_null($this->_collection))
            $this->_collection = Mage::getModel('hipay/paymentProfile')->getCollection();

        return $this->_collection;
    }

    public function splitPaymentsToOptionArray()
    {
        /*$options = array();
        foreach ($this->_getCollection()->addFieldToFilter('payment_type','split_payment') as $profile) {
            $options[$profile->getId()] = $profile->getName();
        }
        
        return $options;
        */
        return $this->_getCollection()->addFieldToFilter('payment_type', 'split_payment')->toOptionArray();
    }

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {

        return $this->_getCollection()->toOptionArray();

    }


}
