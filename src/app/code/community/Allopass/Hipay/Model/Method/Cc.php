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
 *
 * @author      HiPay <support.tpp@hipay.com>
 * @copyright   Copyright (c) 2018 - HiPay
 * @license     https://github.com/hipay/hipay-fullservice-sdk-magento1/blob/master/LICENSE.md
 * @link    https://github.com/hipay/hipay-fullservice-sdk-magento1
 */
class Allopass_Hipay_Model_Method_Cc extends Allopass_Hipay_Model_Method_Abstract
{
    protected $_canReviewPayment = true;
    const STATUS_PENDING_CAPTURE = 'pending_capture';

    protected $_code = 'hipay_cc';

    protected $_formBlockType = 'hipay/form_cc';
    protected $_infoBlockType = 'hipay/info_cc';


    /**
     * Assign data to info model instance
     *
     * @param   mixed $data
     * @return  Mage_Payment_Model_Info
     */
    public function assignData($data)
    {
        if (!($data instanceof Varien_Object)) {
            $data = new Varien_Object($data);
        }

        $info = $this->getInfoInstance();
        $info->setCcType($data->getData($this->getCode() . '_cc_type'))
            ->setCcOwner($data->getData($this->getCode() . '_cc_owner'))
            ->setCcLast4(substr($data->getData($this->getCode() . '_cc_number'), -4))
            ->setCcNumber($data->getData($this->getCode() . '_cc_number'))
            ->setCcExpMonth($data->getData($this->getCode() . '_cc_exp_month'))
            ->setCcExpYear($data->getData($this->getCode() . '_cc_exp_year'))
            ->setCcSsIssue($data->getData($this->getCode() . '_cc_ss_issue'))
            ->setCcSsStartMonth($data->getData($this->getCode() . '_cc_ss_start_month'))
            ->setCcSsStartYear($data->getData($this->getCode() . '_cc_ss_start_year'));

        $this->assignInfoData($info, $data);

        return $this;
    }

    /**
     * Prepare info instance for save
     *
     * @return Mage_Payment_Model_Abstract
     */
    public function prepareSave()
    {
        $info = $this->getInfoInstance();
        if ($this->_canSaveCc) {
            $info->setCcNumberEnc($info->encrypt($info->getCcNumber()));
        }

        $info->setCcNumber(null)
            ->setCcCid(null);
        return $this;
    }


    /**
     * Retrieve payment iformation model object
     *
     * @return Mage_Payment_Model_Info
     */
    public function getInfoInstance()
    {
        $instance = $this->getData('info_instance');
        if (!($instance instanceof Mage_Payment_Model_Info)) {
            Mage::throwException(
                Mage::helper('payment')->__('Cannot retrieve the payment information object instance.')
            );
        }

        return $instance;
    }

    public function getOrderPlaceRedirectUrl()
    {
        return Mage::getUrl('hipay/cc/sendRequest', array('_secure' => true));
    }


    public function initialize($paymentAction, $stateObject)
    {
        /* @var $payment Mage_Sales_Model_Order_Payment */
        $payment = $this->getInfoInstance();

        //Token is already generate by JS Tokenization
        if ($payment->getAdditionalInformation('token') != "") {
            return $this;
        }

        $order = $payment->getOrder();
        $customer = Mage::getModel('customer/customer')->load($order->getCustomerId());
        $token = "";

        if ($payment->getAdditionalInformation('use_oneclick') && $customer->getId()) {
            $cardId = $payment->getAdditionalInformation('selected_oneclick_card');
            $card = Mage::getModel('hipay/card')->load($cardId);
            if ($card->getId() && $card->getCustomerId() == $customer->getId()) {
                $token = $card->getCcToken();
                $payment->setCcOwner($card->getCcOwner());
            } else {
                Mage::throwException(Mage::helper('hipay')->__("Error with your card!"));
            }
        } else {
            Mage::throwException(Mage::helper('hipay')->__("Try to tokenize from server!"));
        }

        $payment->setAdditionalInformation('token', $token);

        return $this;
    }


    /**
     * (non-PHPdoc)
     * @see Mage_Payment_Model_Method_Abstract::capture()
     */
    public function capture(Varien_Object $payment, $amount)
    {

        parent::capture($payment, $amount);

        if ($this->isPreauthorizeCapture($payment)) {
            $this->_preauthorizeCapture($payment, $amount);
        }

        $payment->setSkipTransactionCreation(true);
        return $this;
    }


    public function place($payment, $amount)
    {

        $order = $payment->getOrder();
        $customer = Mage::getModel('customer/customer')->load($order->getCustomerId());

        $request = Mage::getModel('hipay/api_request', array($this));


        $payment->setAmount($amount);

        $token = $payment->getAdditionalInformation('token');
        $gatewayParams = $this->getGatewayParams($payment, $amount, $token);

        $gatewayParams['operation'] = $this->getOperation();

        if ($payment->getAdditionalInformation('use_oneclick')) {
            $cardId = $payment->getAdditionalInformation('selected_oneclick_card');
            $card = Mage::getModel('hipay/card')->load($cardId);

            if ($card->getId() && $card->getCustomerId() == $customer->getId()) {
                $paymentProduct = $card->getCcType();
            } else {
                Mage::throwException(Mage::helper('hipay')->__("Error with your card!"));
            }
        } else {
            $paymentProduct = $this->getSpecifiedPaymentProduct($payment);
        }

        $gatewayParams['payment_product'] = $paymentProduct;
        $this->_debug($gatewayParams);


        $gatewayResponse = $request->gatewayRequest(
            Allopass_Hipay_Model_Api_Request::GATEWAY_ACTION_ORDER,
            $gatewayParams,
            $payment->getOrder()->getStoreId()
        );

        $this->_debug($gatewayResponse->debug());

        $redirectUrl = $this->processResponseToRedirect($gatewayResponse, $payment, $amount);

        return $redirectUrl;

    }

    /**
     *  Some payments method need product code with fees or no fees
     *
     * @return string|bool
     */
    private function getPaymentProductFees()
    {
        $paymentFees = $this->getConfigData('payment_product_fees');
        if (!empty($paymentFees)) {
            return $paymentFees;
        }

        return false;
    }

    /**
     *  Return payment product
     *
     *  If Payment requires specified option ( With Fees or without Fees return it otherwhise normal payment product)
     *
     * @return string
     */
    private function getSpecifiedPaymentProduct($payment)
    {
        return ($this->getPaymentProductFees()) ? $this->getPaymentProductFees() : $this->getCcTypeHipay(
            $payment->getCcType()
        );
    }

    /**
     * Validate payment method information object
     *
     * @return $this
     * @throws Mage_Core_Exception
     */
    public function validate()
    {
        /*
         * calling parent validate function
        */
        parent::validate();

        $info = $this->getInfoInstance();

        if ($info->getAdditionalInformation('use_oneclick')) {
            return $this;
        }

        $errorMsg = false;
        $availableTypes = explode(',', $this->getConfigData('cctypes'));

        $ccNumber = $info->getCcNumber();

        // remove credit card number delimiters such as "-" and space
        $ccNumber = preg_replace('/[\-\s]+/', '', $ccNumber);
        $info->setCcNumber($ccNumber);

        $ccType = '';

        if (!in_array($info->getCcType(), $availableTypes)) {
            $errorMsg = Mage::helper('payment')->__('Credit card type is not allowed for this payment method.');
        }

        if ($ccType != 'SS' && !$this->_validateExpDate($info->getCcExpYear(), $info->getCcExpMonth())) {
            $errorMsg = Mage::helper('payment')->__('Incorrect credit card expiration date.');
        }

        if ($errorMsg) {
            Mage::throwException($errorMsg);
        }

        return $this;
    }

    public function hasVerification()
    {
        $configData = $this->getConfigData('useccv');
        if ($configData === null) {
            return true;
        }

        return (bool)$configData;
    }


    protected function _validateExpDate($expYear, $expMonth)
    {
        $date = Mage::app()->getLocale()->date();
        if (!$expYear
            || !$expMonth
            || ($date->compareYear($expYear) == 1)
            || ($date->compareYear($expYear) == 0 && ($date->compareMonth($expMonth) == 1))
        ) {
            return false;
        }

        return true;
    }

    /**
     * Check whether there are CC types set in configuration
     *
     * @param Mage_Sales_Model_Quote|null $quote
     * @return bool
     */
    public function isAvailable($quote = null)
    {
        return $this->getConfigData('cctypes', ($quote ? $quote->getStoreId() : null))
            && !$this->getHiPayConfig()->publicCredentialsEmpty(($quote ? $quote->getStoreId() : null))
            && parent::isAvailable($quote);
    }

    /**
     * Whether current operation is order placement
     *
     * @return bool
     */
    private function _isPlaceOrder()
    {
        $info = $this->getInfoInstance();
        if ($info instanceof Mage_Sales_Model_Quote_Payment) {
            return false;
        } elseif ($info instanceof Mage_Sales_Model_Order_Payment) {
            return true;
        }
    }

    private function getHiPayConfig()
    {
        return Mage::getSingleton('hipay/config');
    }

}
