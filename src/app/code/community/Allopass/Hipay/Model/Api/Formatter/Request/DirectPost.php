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
 * @copyright   Copyright (c) 2018 - HiPay
 * @license     https://github.com/hipay/hipay-fullservice-sdk-magento1/blob/master/LICENSE.md
 * @link    https://github.com/hipay/hipay-fullservice-sdk-magento1
 */
class Allopass_Hipay_Model_Api_Formatter_Request_DirectPost extends Allopass_Hipay_Model_Api_Formatter_Request_OrderRequestAbstract
{

    protected $_paymentProduct;
    protected $_paymentMethodFormatter;
    protected $_deviceFingerprint;

    public function __construct($args)
    {
        parent::__construct($args);
        $this->_paymentProduct = $args["paymentProduct"];
        $this->_paymentMethodFormatter = $args["paymentMethodFormatter"];
        $this->_deviceFingerprint = $args["deviceFingerprint"];
    }

    public function generate()
    {
        $order = new \HiPay\Fullservice\Gateway\Request\Order\OrderRequest();

        $this->mapRequest($order);

        return $order;
    }

    public function mapRequest(&$order)
    {
        parent::mapRequest($order);
        $order->payment_product = $this->_paymentProduct;
        $order->device_fingerprint = $this->_deviceFingerprint;
        $order->paymentMethod = $this->_paymentMethodFormatter;
        $this->getCustomerNames($order);
    }

    /**
     * @param $order
     */
    protected function getCustomerNames(&$order)
    {

        $names = explode(' ', trim($this->_payment->getCcOwner()));

        if (
            ($this->_payment->getCcType() === "AE" || $this->_payment->getCcType() === "american-express")
            && count($names) > 1
        ) {
            $order->firstname = $names[0];
            $order->lastname = trim(preg_replace('/' . $names[0] . '/', "", $this->_payment->getCcOwner(), 1));
        }
    }
}
