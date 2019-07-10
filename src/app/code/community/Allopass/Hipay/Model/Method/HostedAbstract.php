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
abstract class Allopass_Hipay_Model_Method_HostedAbstract extends Allopass_Hipay_Model_Method_Abstract
{

    protected $_canReviewPayment = true;

    protected $_formBlockType = 'hipay/form_hosted';
    protected $_infoBlockType = 'hipay/info_hosted';

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
        $this->assignInfoData($info, $data);

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
        $request = Mage::getModel(
            'hipay/api_api',
            array(
                "paymentMethod" => $this,
                "payment" => $payment,
                "amount" => $amount
            )
        );

        $response = $request->getHostedPaymentPage(
            $this->getPaymentProductList($payment),
            $this->getAdditionalParameters($payment)
        );

        return $response->getForwardUrl();
    }

    public function getPaymentProductList($payment)
    {
        return $this->getConfigData('cctypes');
    }
}
