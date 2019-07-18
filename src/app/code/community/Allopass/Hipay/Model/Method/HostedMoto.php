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
class Allopass_Hipay_Model_Method_HostedMoto extends Allopass_Hipay_Model_Method_HostedAbstract
{
    protected $_canUseInternal = true;
    protected $_canUseCheckout = false;
    protected $_code = 'hipay_hostedmoto';

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

    public function place($payment, $amount)
    {
        $payment->setAdditionalInformation("isMoto", true);

        return parent::place($payment, $amount);
    }

    protected function getAdditionalParameters($payment)
    {
        return array("isMoto" => true);
    }
}
