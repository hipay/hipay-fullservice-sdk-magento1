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
 * Source model for signature notification
 *
 * @author Aymeric Berthelot <aberthelot@hipay.com>
 * @copyright   Copyright (c) 2018 - HiPay
 * @license     https://github.com/hipay/hipay-fullservice-sdk-magento1/blob/master/LICENSE.md
 * @link    https://github.com/hipay/hipay-fullservice-sdk-magento1
 */

class Allopass_Hipay_Model_Source_HostedpageVersion
{
    const V1 = 'hpv1';
    const V2 = 'hpv2';

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => self::V1, 'label' => 'Hosted Page v1'),
            array('value' => self::V2, 'label' => 'Hosted Page v2'),
        );
    }
}
