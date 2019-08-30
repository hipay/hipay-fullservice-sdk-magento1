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
class Allopass_Hipay_Model_Api_Formatter_Info_CustomerBillingInfoFormatter implements Allopass_Hipay_Model_Api_Formatter_ApiFormatterInterface
{

    protected $_paymentMethod;
    protected $_payment;
    protected $_order;

    public function __construct($args)
    {
        $this->_paymentMethod = $args["paymentMethod"];
        $this->_payment = $args["payment"];
        $this->_order = $this->_payment->getOrder();
    }

    /**
     * return mapped customer billing information
     *
     * @return \HiPay\Fullservice\Gateway\Request\Info\CustomerBillingInfoRequest|mixed
     * @throws Hipay_Payment_Exception
     */
    public function generate()
    {
        $customerBillingInfo = new \HiPay\Fullservice\Gateway\Request\Info\CustomerBillingInfoRequest();

        $this->mapRequest($customerBillingInfo);

        return $customerBillingInfo;
    }

    /**
     * Map billing information to request fields (Hpayment Post)
     *
     * @param \HiPay\Fullservice\Gateway\Request\Info\CustomerBillingInfoRequest $customerBillingInfo
     * @return mixed|void
     * @throws Hipay_Payment_Exception
     */
    public function mapRequest(&$customerBillingInfo)
    {
        $customerBillingInfo->firstname = $this->_order->getCustomerFirstname();
        $customerBillingInfo->lastname = $this->_order->getCustomerLastname();
        $customerBillingInfo->email = $this->_order->getCustomerEmail();

        $customerBillingInfo->country = $this->_order->getBillingAddress()->getCountry();

        $customerBillingInfo->streetaddress = $this->_order->getBillingAddress()->getStreet1();
        $customerBillingInfo->streetaddress2 = $this->_order->getBillingAddress()->getStreet2();
        $customerBillingInfo->city = $this->_order->getBillingAddress()->getCity();
        $customerBillingInfo->state = $this->getState();

        $customerBillingInfo->zipcode = $this->getZipCode();

        $customerBillingInfo->phone = $this->getPhone();
        $customerBillingInfo->birthdate = $this->getDateOfBirth();
        $customerBillingInfo->gender = $this->getGender();
        $customerBillingInfo->recipientinfo = $this->_order->getBillingAddress()->getCompany();

    }

    protected function getState()
    {
        if ($this->_order->getBillingAddress()->getRegionCode()) {
            return $this->_order->getBillingAddress()->getRegionCode();
        }

        return $this->_order->getBillingAddress()->getCity();
    }

    protected function getZipCode()
    {
        $zipCode = explode('-', $this->_order->getBillingAddress()->getPostcode());
        return $zipCode[0];
    }

    protected function getPhone()
    {

        $phone = $this->_order->getBillingAddress()->getTelephone();

        if ($this->_payment->getCcType() == 'bnpp-3xcb' ||
            $this->_payment->getCcType() == 'bnpp-4xcb' ||
            $this->_payment->getCcType() == 'credit-long'
        ) {
            $phone = preg_replace('/^(\+33)|(33)/', '0', $phone);
        }

        return $phone;
    }

    protected function getDateOfBirth()
    {

        $dob = $this->_order->getCustomerDob();

        if ($dob != "") {
            $dob = new Zend_Date($dob);
            $validator = new Zend_Validate_Date();
            if ($validator->isValid($dob)) {
                return $dob->toString('YYYYMMdd');
            }
        }

        return null;
    }

    protected function getGender()
    {
        if ($this->_payment->getCcType() == 'bnpp-3xcb' ||
            $this->_payment->getCcType() == 'bnpp-4xcb' ||
            $this->_payment->getCcType() == 'credit-long'
        ) {
            return 'M';
        }

        $gender = $this->_order->getCustomerGender();

        $customer = Mage::getModel('customer/customer');
        $customer->setData('gender', $gender);
        $attribute = $customer->getResource()->getAttribute('gender');

        if ($attribute) {
            $gender = $attribute->getFrontend()->getValue($customer);
            $gender = strtoupper(substr($gender, 0, 1));
        }

        if ($gender != "M" && $gender != "F") {
            $gender = "U";
        }

        return $gender;
    }
}
