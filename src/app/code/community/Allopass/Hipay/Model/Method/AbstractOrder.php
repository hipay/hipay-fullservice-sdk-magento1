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
class Allopass_Hipay_Model_Method_AbstractOrder extends Allopass_Hipay_Model_Method_Cc
{
    protected $_formBlockType = 'hipay/form_hosted';
    protected $_infoBlockType = 'hipay/info_hosted';


    public function getOrderPlaceRedirectUrl()
    {
        return Mage::getUrl(str_replace("_", "/", $this->getCode()) . '/sendRequest', array('_secure' => true));
    }

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
        $info->setCcType($this->getConfigData('cctypes'));

        $this->assignInfoData($info, $data);

        return $this;
    }

    /**
     * @param string $paymentAction
     * @param object $stateObject
     * @return $this
     * @throws Mage_Core_Exception
     */
    public function initialize($paymentAction, $stateObject)
    {
        /* @var $payment Mage_Sales_Model_Order_Payment */
        $payment = $this->getInfoInstance();
        $order = $payment->getOrder();
        $customer = Mage::getModel('customer/customer')->load($order->getCustomerId());


        if ($payment->getAdditionalInformation('use_oneclick') && $customer->getId()) {
            $cardId = $payment->getAdditionalInformation('selected_oneclick_card');
            $card = Mage::getModel('hipay/card')->load($cardId);
            if ($card->getId() && $card->getCustomerId() == $customer->getId()) {
                $token = $card->getCcToken();
            } else {
                Mage::throwException(Mage::helper('hipay')->__("Error with your card!"));
            }

            $payment->setAdditionalInformation('token', $token);
        }

        return $this;

    }


    protected function getCcTypeHipay($ccTypeMagento)
    {
        return $ccTypeMagento;
    }

    /**
     * Validate payment method information object
     *
     * @return $this
     * @throws Mage_Core_Exception
     */
    public function validate()
    {
        /**
         * to validate payment method is allowed for billing country or not
         */
        $paymentInfo = $this->getInfoInstance();
        if ($paymentInfo instanceof Mage_Sales_Model_Order_Payment) {
            $billingCountry = $paymentInfo->getOrder()->getBillingAddress()->getCountryId();
        } else {
            $billingCountry = $paymentInfo->getQuote()->getBillingAddress()->getCountryId();
        }

        if (!$this->canUseForCountry($billingCountry)) {
            Mage::throwException(
                Mage::helper('payment')->__('Selected payment type is not allowed for billing country.')
            );
        }

        return $this;
    }

}
