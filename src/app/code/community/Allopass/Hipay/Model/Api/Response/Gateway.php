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
 * @method string getState() transaction state. completed, forwarding, pending, declined, error
 * @method array getReason() optional element. Reason why transaction was declined.
 * @method bool getTest() true if the transaction is a testing transaction, otherwise false
 * @method int getMid() your merchant account number (issued to you by Allopass).
 * @method string getStatus() transaction status.
 * @method string getMessage() transaction message.
 * @method string getDecimals() decimal precision of transaction amount..
 * @method string getCurrency() base currency for this transaction.
 * @method string getEci() Electronic Commerce Indicator (ECI).
 *
 * @author      HiPay <support.tpp@hipay.com>
 * @copyright   Copyright (c) 2018 - HiPay
 * @license     https://github.com/hipay/hipay-fullservice-sdk-magento1/blob/master/LICENSE.md
 * @link    https://github.com/hipay/hipay-fullservice-sdk-magento1
 */
class Allopass_Hipay_Model_Api_Response_Gateway extends Allopass_Hipay_Model_Api_Response_Abstract
{
    public function getForwardUrl()
    {
        return $this->getData('forwardUrl');
    }

    public function getAttemptId()
    {
        return $this->getData('attemptId');
    }

    public function getAuthorizationCode()
    {
        return $this->getData('authorizationCode');
    }


    public function getTransactionReference()
    {
        if ($this->getData('transactionReference') == '') {
            return $this->getData('reference');
        }

        return $this->getData('transactionReference');
    }


    public function getDateCreated()
    {
        return $this->getData('dateCreated');
    }


    public function getDateUpdated()
    {
        return $this->getData('dateUpdated');
    }


    public function getDateAuthorized()
    {
        return $this->getData('dateAuthorized');
    }

    public function getAuthorizedAmount()
    {
        return $this->getData('authorizedAmount');
    }

    public function getCapturedAmount()
    {
        return $this->getData('capturedAmount');
    }

    public function getRefundedAmount()
    {
        return $this->getData('refundedAmount');
    }

    public function getIpAddress()
    {
        return $this->getData('ipAddress');
    }

    public function getIpCountry()
    {
        return $this->getData('ipCountry');
    }

    public function getPaymentProduct()
    {
        return $this->getData('paymentProduct');
    }

    public function getPaymentMethod()
    {
        return $this->getData('paymentMethod');
    }

    public function getFraudScreening()
    {
        return $this->getData('fraudScreening');
    }

}
