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

use HiPay\Fullservice\Gateway\Request\PaymentMethod\SEPADirectDebitPaymentMethod;

/**
 *
 *
 * @author      HiPay <support.tpp@hipay.com>
 * @copyright   Copyright (c) 2018 - HiPay
 * @license     https://github.com/hipay/hipay-fullservice-sdk-magento1/blob/master/LICENSE.md
 * @link    https://github.com/hipay/hipay-fullservice-sdk-magento1
 */
class Allopass_Hipay_Model_Method_Sdd extends Allopass_Hipay_Model_Method_AbstractOrderApi
{
    protected $_code = 'hipay_sdd';
    protected $_formBlockType = 'hipay/form_sdd';
    protected $_canCapture = false;
    protected $_canCapturePartial = false;
    protected $_canRefund = false;
    protected $_canRefundInvoicePartial = false;

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
        $info->setCcType('SDD')
             ->setAdditionalInformation('cc_gender', $data->getCcGender())
             ->setAdditionalInformation('cc_firstname', $data->getCcFirstname())
             ->setAdditionalInformation('cc_lastname', $data->getCcLastname())
             ->setAdditionalInformation('cc_iban', $data->getCcIban())
             ->setAdditionalInformation('cc_code_bic', $data->getCcCodeBic())
             ->setAdditionalInformation('cc_bank_name', $data->getCcBankName());

        $this->assignInfoData($info, $data);

        return $this;
    }

    public function getPaymentMethodFormatter($payment)
    {
        $paymentMethod = new SEPADirectDebitPaymentMethod();
        $paymentMethod->gender = $payment->getAdditionalInformation('cc_gender');
        $paymentMethod->firstname = $payment->getAdditionalInformation('cc_firstname');
        $paymentMethod->lastname = $payment->getAdditionalInformation('cc_lastname');
        $paymentMethod->bank_name = $payment->getAdditionalInformation('cc_bank_name');
        $paymentMethod->iban = $payment->getAdditionalInformation('cc_iban');
        $paymentMethod->issuer_bank_id = $payment->getAdditionalInformation('cc_code_bic');
        $paymentMethod->recurring_payment = 0;
        $paymentMethod->authentication_indicator = 0;

        return $paymentMethod;
    }

    /**
     *
     * @return $this
     * @throws Mage_Core_Exception
     */
    public function validate()
    {
        /**
         * to validate payment method is allowed for billing country or not
         */
        $errorMsg = '';
        $paymentInfo = $this->getInfoInstance();

        $iban = new Zend_Validate_Iban();
        if (!$iban->isValid($paymentInfo->getAdditionalInformation('cc_iban'))) {
            $errorMsg = Mage::helper('payment')->__('Iban is not correct, please enter a valid Iban.');
        }

        // variable pour la fonction empty
        $ccFirstName = $paymentInfo->getAdditionalInformation('cc_firstname');
        $ccLastName = $paymentInfo->getAdditionalInformation('cc_lastname');
        $ccCodeBic = $paymentInfo->getAdditionalInformation('cc_code_bic');
        $ccBankName = $paymentInfo->getAdditionalInformation('cc_bank_name');
        if (empty($ccFirstName)) {
            $errorMsg = Mage::helper('payment')->__('Firstname is mandatory.');
        }

        if (empty($ccLastName)) {
            $errorMsg = Mage::helper('payment')->__('Lastname is mandatory.');
        }

        if (empty($ccCodeBic)) {
            $errorMsg = Mage::helper('payment')->__('Code BIC is not correct, please enter a valid Code BIC.');
        }

        if (empty($ccBankName)) {
            $errorMsg = Mage::helper('payment')->__('Bank name is not correct, please enter a valid Bank name.');
        }

        if ($errorMsg) {
            Mage::throwException($errorMsg);
        }

        return $this;
    }
}
