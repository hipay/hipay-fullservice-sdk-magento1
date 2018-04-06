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
 * @method string getToken() card token
 * @method string getBrand() card type
 * @method string getPan() card number masked
 * @method string getCardHolder() Cardholder name
 * @method int getCardExpiryMonth() card expiry month (2 digits)
 * @method int getCardExpiryYear() card expiry year (4 digits)
 * @method string getIssuer() card issuing bank name
 * @method string getCountry() bank country code (ISO 3166-1, 2 letters)
 *
 * @author      HiPay <support.tpp@hipay.com>
 * @copyright   Copyright (c) 2018 - HiPay
 * @license     https://github.com/hipay/hipay-fullservice-sdk-magento1/blob/master/LICENSE.md
 * @link    https://github.com/hipay/hipay-fullservice-sdk-magento1
 */
abstract class Allopass_Hipay_Model_Api_Response_Abstract extends Varien_Object
{

}
