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
 * @method int getAttemptId() attempt id of the payment.
 * @method string getAuthorizationCode() an authorization code (up to 35 characters) generated for each approved or pending transaction by the acquiring provider.
 * @method string getTransactionReference() the unique identifier of the transaction.
 * @method DateTime getDateCreated() time when transaction was created.
 * @method DateTime getDateUpdated() time when transaction was last updated.
 * @method DateTime getDateAuthorized() time when transaction was authorized.
 * @method string getStatus() transaction status.
 * @method string getMessage() transaction message.
 * @method string getAuthorizedAmount() the transaction amount.
 * @method string getCapturedAmount() captured amount.
 * @method string getRefundedAmount() refunded amount.
 * @method string getDecimals() decimal precision of transaction amount..
 * @method string getCurrency() base currency for this transaction.
 * @method string getIpAddress() the IP address of the customer making the purchase.
 * @method string getIpCountry() country code associated to the customer's IP address.
 * @method string getEci() Electronic Commerce Indicator (ECI).
 * @method string getPaymentProduct() payment product used to complete the transaction.
 * @method string getPaymentMethod() base currency for this transaction.
 * @method array getFraudScreening() Result of the fraud screening.
 *
 * @author      HiPay <support.tpp@hipay.com>
 * @copyright   Copyright (c) 2018 - HiPay
 * @license     https://github.com/hipay/hipay-fullservice-sdk-magento1/blob/master/LICENSE.md
 * @link    https://github.com/hipay/hipay-fullservice-sdk-magento1
 */
class Allopass_Hipay_Model_Api_Response_Notification extends Allopass_Hipay_Model_Api_Response_Abstract
{


}
