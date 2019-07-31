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
class Allopass_Hipay_Model_Api_Formatter_Cart_DeliveryInformationFormatter implements Allopass_Hipay_Model_Api_Formatter_ApiFormatterInterface
{
    protected $_paymentMethod;
    protected $_payment;
    protected $_order;
    protected $_store;

    public function __construct($args)
    {
        $this->_paymentMethod = $args["paymentMethod"];
        $this->_payment = $args["payment"];
        $this->_order = $this->_payment->getOrder();
        $this->_store = Mage::app()->getStore();
    }

    /**
     * Return  mapped delivery shipping information
     *
     * @return \HiPay\Fullservice\Gateway\Request\Info\DeliveryShippingInfoRequest|mixed
     * @throws Exception
     */
    public function generate()
    {
        $deliveryShippingInfo = new \HiPay\Fullservice\Gateway\Request\Info\DeliveryShippingInfoRequest();

        $this->mapRequest($deliveryShippingInfo);

        return $deliveryShippingInfo;
    }

    /**
     * Map  delivery shipping information to request fields (Hpayment Post)
     *
     * @param \HiPay\Fullservice\Gateway\Request\Info\DeliveryShippingInfoRequest $deliveryShippingInfo
     * @return mixed|void
     * @throws Exception
     */
    public function mapRequest(&$deliveryShippingInfo)
    {
        $mapping = Mage::helper('hipay')->getMappingShipping(
            $this->_payment->getOrder()->getShippingMethod(),
            $this->_store->getId()
        );

        $deliveryShippingInfo->delivery_date = Mage::helper('hipay')->calculateEstimatedDate($mapping);
        $deliveryShippingInfo->delivery_method = Mage::helper('hipay')->getMappingShippingMethod($mapping);

        if (empty($deliveryShippingInfo->delivery_date)) {
            Mage::helper('hipay')->debug('### Method processDeliveryInformation');
            Mage::helper('hipay')->debug(
                '### WARNING : Mapping for ' . $this->_payment->getOrder()->getShippingMethod() . ' is missing.'
            );
        }
    }
}
