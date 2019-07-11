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
     * @param $payment
     * @param $amount
     * @return bool|string
     */
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

        $response = $request->requestDirectPost(
            $this->getSpecifiedPaymentProduct($payment),
            $this->getPaymentMethodFormatter($payment),
            $payment->getAdditionalInformation('device_fingerprint'),
            $this->getAdditionalParameters($payment)
        );

        return $this->handleApiResponse($response, $payment);
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
        return $payment->getCcType();
    }

    public function getPaymentMethodFormatter($payment)
    {
        return null;
    }

    public function getAdditionalParameters($payment)
    {
        return null;
    }
}
