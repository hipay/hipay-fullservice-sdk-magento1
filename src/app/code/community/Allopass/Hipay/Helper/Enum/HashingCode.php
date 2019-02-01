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
 *  Hashing code available for Hipay Signature Notifications
 *
 * @author Aymeric Berthelot <aberthelot@hipay.com>
 * @copyright Copyright (c) 2018 - HiPay
 * @license     https://github.com/hipay/hipay-fullservice-sdk-magento1/blob/master/LICENSE.md
 * @link    https://github.com/hipay/hipay-fullservice-sdk-magento1
 */
abstract class HashingCode
{
    /**
     * Hashing SHA-1
     *
     */
    const SHA1 = 'SHA1';

    /**
     * Hashing SHA-256
     */
    const SHA256 = 'SHA256';

    /**
     * Hashing SHA-512
     */
    const SHA512 = 'SHA512';

}
