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
 * Hipay resource collection model
 *
 * @author      HiPay <support.tpp@hipay.com>
 * @copyright   Copyright (c) 2018 - HiPay
 * @license     https://github.com/hipay/hipay-fullservice-sdk-magento1/blob/master/LICENSE.md
 * @link    https://github.com/hipay/hipay-fullservice-sdk-magento1
 */
class Allopass_Hipay_Model_Resource_PaymentProfile_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{

    /**
     * Set resource model and determine field mapping
     */
    protected function _construct()
    {
        $this->_init('hipay/paymentProfile');
    }


    public function toOptionArray()
    {
        return $this->_toOptionArray('profile_id');
    }

    /**
     * Add filtering by profile ids
     *
     * @param mixed $profileIds
     * @return Allopass_Hipay_Model_Resource_PaymentProfile_Collection
     */
    public function addIdsToFilter($profileIds)
    {
        $this->addFieldToFilter('main_table.profile_id', $profileIds);
        return $this;
    }

}
