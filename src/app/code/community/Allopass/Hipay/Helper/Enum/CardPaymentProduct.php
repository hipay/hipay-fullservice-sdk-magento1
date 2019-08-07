<?php
/**
 * HiPay Fullservice SDK Magento 1
 *
 * 2019 HiPay
 *
 * NOTICE OF LICENSE
 *
 * @author    HiPay <support.tpp@hipay.com>
 * @copyright 2019 HiPay
 * @license   https://github.com/hipay/hipay-fullservice-sdk-magento1/blob/master/LICENSE.md
 */

/**
 *
 * @author      HiPay <support.tpp@hipay.com>
 * @copyright   Copyright (c) 2019 - HiPay
 * @license     https://github.com/hipay/hipay-fullservice-sdk-magento1/blob/master/LICENSE.md
 * @link    https://github.com/hipay/hipay-fullservice-sdk-magento1
 */
class CardPaymentProduct
{
    const CC = 'hipay_cc';
    const CC_SPLIT_PAYMENT = 'hipay_ccxtimes';
    const CC_HOSTED = 'hipay_hosted';
    const CC_HOSTED_SPLIT_PAYMENT = 'hipay_hostedxtimes';
    const CC_HOSTED_FIELDS = 'hipay_hostedfields';
    const CC_HOSTED_FIELDS_SPLIT_PAYMENT = 'hipay_hostedfieldsxtimes';

    const threeDS2Available = array(
        self::CC,
        self::CC_SPLIT_PAYMENT,
        self::CC_HOSTED,
        self::CC_HOSTED_SPLIT_PAYMENT,
        self::CC_HOSTED_FIELDS,
        self::CC_HOSTED_FIELDS_SPLIT_PAYMENT
    );
}
