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
class Allopass_Hipay_Model_Api_Formatter_ThreeDS_BrowserInfoFormatter implements Allopass_Hipay_Model_Api_Formatter_ApiFormatterInterface
{

    protected $_paymentMethod;
    protected $_payment;
    /**
     * @var Mage_Sales_Model_Order $_order
     */
    protected $_order;

    public function __construct($args)
    {
        $this->_paymentMethod = $args["paymentMethod"];
        $this->_payment = $args["payment"];
        $this->_order = $this->_payment->getOrder();
    }

    /**
     * @return \HiPay\Fullservice\Gateway\Model\Request\ThreeDSTwo\BrowserInfo
     */
    public function generate()
    {
        $browserInfo = new \HiPay\Fullservice\Gateway\Model\Request\ThreeDSTwo\BrowserInfo();

        $this->mapRequest($browserInfo);

        return $browserInfo;
    }

    /**
     * @param \HiPay\Fullservice\Gateway\Model\Request\ThreeDSTwo\BrowserInfo $browserInfo
     */
    public function mapRequest(&$browserInfo)
    {
        $browserInfo->ipaddr = isset($_SERVER['HTTP_CLIENT_IP']) ? $_SERVER['HTTP_CLIENT_IP'] : null;
        $browserInfo->http_accept = isset($_SERVER['HTTP_ACCEPT']) ? $_SERVER['HTTP_ACCEPT'] : null;

        $rawBrowserInfo = json_decode($this->_payment->getAdditionalInformation('browser_info'));

        if($rawBrowserInfo !== false) {
            $browserInfo->javascript_enabled = true;

            $browserInfo->java_enabled = isset($rawBrowserInfo->java_enabled) ? $rawBrowserInfo->java_enabled : null;
            $browserInfo->language = isset($rawBrowserInfo->language) ? $rawBrowserInfo->language : null;
            $browserInfo->color_depth = isset($rawBrowserInfo->color_depth) ? $rawBrowserInfo->color_depth : null;
            $browserInfo->screen_height = isset($rawBrowserInfo->screen_height) ? $rawBrowserInfo->screen_height : null;
            $browserInfo->screen_width = isset($rawBrowserInfo->screen_width) ? $rawBrowserInfo->screen_width : null;
            $browserInfo->timezone = isset($rawBrowserInfo->timezone) ? $rawBrowserInfo->timezone : null;
            $browserInfo->http_user_agent = isset($rawBrowserInfo->http_user_agent) ? $rawBrowserInfo->http_user_agent : null;
            $browserInfo->device_fingerprint = $this->_payment->getAdditionalInformation('device_fingerprint');
        } else {
            $browserInfo->javascript_enabled = false;
        }
    }
}