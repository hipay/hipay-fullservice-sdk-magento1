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
class Allopass_Hipay_Model_Resource_Rule_Collection extends Mage_Rule_Model_Mysql4_Rule_Collection
{

    /**
     * Set resource model and determine field mapping
     */
    protected function _construct()
    {
        $this->_init('hipay/rule');
        $this->_map['fields']['rule_id'] = 'main_table.rule_id';
    }

}
