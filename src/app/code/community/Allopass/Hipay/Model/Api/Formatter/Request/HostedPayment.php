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
class Allopass_Hipay_Model_Api_Formatter_Request_HostedPayment extends Allopass_Hipay_Model_Api_Formatter_Request_OrderRequestAbstract
{

    protected $_productList;

    public function __construct($args)
    {
        parent::__construct($args);
        $this->_productList = $args["productList"];
    }

    public function generate()
    {
        $order = new \HiPay\Fullservice\Gateway\Request\Order\HostedPaymentPageRequest();

        $this->mapRequest($order);

        return $order;
    }

    public function mapRequest(&$order)
    {
        parent::mapRequest($order);

        if ($this->_paymentMethod->getConfigData('display_iframe')) {
            $order->template = "iframe-js";
        } else {
            $order->template = $this->_paymentMethod->getConfigData('template');
        }

        $order->css = $this->_paymentMethod->getConfigData('css_url');
        $order->display_selector = $this->_paymentMethod->getConfigData('display_selector');
        $order->payment_product_list = $this->_productList;
        $order->payment_product_category_list = null;
        $order->time_limit_to_pay = Mage::helper('hipay')->convertHoursToSecond(
            $this->_paymentMethod->getConfigData('time_limit_to_pay')
        );

        if (Mage::getStoreConfig('general/store_information/name') != "") {
            $order->merchant_display_name = Mage::getStoreConfig('general/store_information/name');
        }

        if ($this->_payment->getAdditionalInformation('create_oneclick')) {
            $order->multi_use = 1;
        }

        if (isset($this->_additionalParameters["authentication_indicator"])) {
            $order->authentication_indicator = $this->_additionalParameters["authentication_indicator"];
        }
    }
}
