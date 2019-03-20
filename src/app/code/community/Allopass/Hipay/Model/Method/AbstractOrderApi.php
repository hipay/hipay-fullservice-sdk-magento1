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
class Allopass_Hipay_Model_Method_AbstractOrderApi extends Allopass_Hipay_Model_Method_Abstract
{
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
        $info->setCcType($this->getConfigData('cctypes'));

        $this->assignInfoData($info, $data);

        return $this;
    }

    /**
     * @param $ccTypeMagento
     * @return mixed
     */
    protected function getCcTypeHipay($ccTypeMagento)
    {
        return $ccTypeMagento;
    }


    /**
     * @param $payment
     * @param $amount
     * @return bool|string
     */
    public function place($payment, $amount)
    {
        $request = Mage::getModel('hipay/api_request', array($this));

        $payment->setAmount($amount);

        $gatewayParams = $this->getGatewayParams($payment, $amount, "");
        $gatewayParams['operation'] = $this->getOperation();
        $gatewayParams['payment_product'] = $this->getSpecifiedPaymentProduct($payment);
        $this->_debug($gatewayParams);

        $gatewayResponse = $request->gatewayRequest(
            Allopass_Hipay_Model_Api_Request::GATEWAY_ACTION_ORDER,
            $gatewayParams,
            $payment->getOrder()->getStoreId()
        );

        $this->_debug($gatewayResponse->debug());
        return $this->processResponseToRedirect($gatewayResponse, $payment, $amount);
    }

    /**
     *  Return payment product
     *
     *  If Payment requires specified option ( With Fees or without Fees return it otherwhise normal payment product)
     *
     * @return string
     */
    public function getSpecifiedPaymentProduct($payment)
    {
        return ($this->getPaymentProductFees()) ? $this->getPaymentProductFees() : $this->getCcTypeHipay(
            $payment->getCcType()
        );
    }

}
