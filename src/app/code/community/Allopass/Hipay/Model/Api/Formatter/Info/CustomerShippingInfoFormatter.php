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
 * @author      HiPay <support.tpp@hipay.com>
 * @copyright   Copyright (c) 2019 - HiPay
 * @license     https://github.com/hipay/hipay-fullservice-sdk-magento1/blob/master/LICENSE.md
 * @link    https://github.com/hipay/hipay-fullservice-sdk-magento1
 */
class Allopass_Hipay_Model_Api_Formatter_Info_CustomerShippingInfoFormatter implements Allopass_Hipay_Model_Api_Formatter_ApiFormatterInterface
{

    protected $_paymentMethod;
    protected $_payment;
    protected $_order;

    public function __construct($args)
    {
        $this->_paymentMethod = $args["paymentMethod"];
        $this->_payment = $args["payment"];
        $this->_order = $this->_paymentMethod->getOrder();
    }

    /**
     * return mapped customer shipping information
     *
     * @return \HiPay\Fullservice\Gateway\Request\Info\CustomerShippingInfoRequest
     */
    public function generate()
    {
        $customerShippingInfo = new \HiPay\Fullservice\Gateway\Request\Info\CustomerShippingInfoRequest();

        $this->mapRequest($customerShippingInfo);

        return $customerShippingInfo;
    }

    /**
     * Map shipping information to request fields (Hpayment Post)
     *
     * @param \HiPay\Fullservice\Gateway\Request\Info\CustomerShippingInfoRequest $customerShippingInfo
     */
    public function mapRequest(&$customerShippingInfo)
    {
        $shippingAddress = $this->_payment->getOrder()->getShippingAddress();

        $customerShippingInfo->shipto_firstname = $shippingAddress->getFirstname();
        $customerShippingInfo->shipto_lastname = $shippingAddress->getLastname();
        $customerShippingInfo->shipto_recipientinfo = $shippingAddress->getCompany();
        $customerShippingInfo->shipto_country = $shippingAddress->getCountry();
        $customerShippingInfo->shipto_streetaddress = $shippingAddress->getStreet1();
        $customerShippingInfo->shipto_streetaddress2 = $shippingAddress->getStreet2();
        $customerShippingInfo->shipto_city = $shippingAddress->getCity();
        $customerShippingInfo->shipto_state = $this->getState($shippingAddress);
        $customerShippingInfo->shipto_zipcode = $shippingAddress->getPostcode();
        $customerShippingInfo->shipto_phone = $shippingAddress->getTelephone();
    }

    protected function getState($shippingAddress)
    {
        return $shippingAddress->getRegionCode() ? $shippingAddress->getRegionCode() : $shippingAddress->getCity();
    }
}
