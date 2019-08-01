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
 * @method getCustomerId() int
 * @method getName() string
 * @method getCcExpMonth() int
 * @method getCcExpYear() int
 * @method getCcSecureVerify() int
 * @method getCclast4() int
 * @method getCcOwner() string
 * @method getCcType() string
 * @method getCcNumberEnc() string
 * @method getCcStatus() int
 * @method getCcToken() string
 * @method getIsDefault() bool
 * @method getCreatedAt() string
 *
 * @author      HiPay <support.tpp@hipay.com>
 * @copyright   Copyright (c) 2018 - HiPay
 * @license     https://github.com/hipay/hipay-fullservice-sdk-magento1/blob/master/LICENSE.md
 * @link    https://github.com/hipay/hipay-fullservice-sdk-magento1
 */
class Allopass_Hipay_Model_Card extends Mage_Core_Model_Abstract
{

    const STATUS_DISABLED = 0;
    const STATUS_ENABLED = 1;

    /**
     * Init resource model and id field
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('hipay/card');
        $this->setIdFieldName('card_id');
    }


}
