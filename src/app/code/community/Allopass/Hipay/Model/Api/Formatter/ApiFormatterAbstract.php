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
abstract class Allopass_Hipay_Model_Api_Formatter_ApiFormatterAbstract implements Allopass_Hipay_Model_Api_Formatter_ApiFormatterInterface
{

    protected $_cartFormatterClass = '';

    /**
     * @var Mage_Payment_Model_Method_Abstract
     */
    protected $_paymentMethod;

    protected $_payment;

    protected $_amount;

    public function __construct($args)
    {
        $this->_paymentMethod = $args["paymentMethod"];
        $this->_payment = $args["payment"];
        $this->_amount = $args["amount"];
    }

    abstract public function generate();

    /**
     * map order information to request fields
     * (shared information between Hpayment, Iframe, Direct Post and Maintenance )
     * @param HiPay\Fullservice\Gateway\Request\CommonRequest $request
     */
    public function mapRequest(&$request)
    {
        $request->source = $this->getRequestSource();
    }

    protected function setCustomData(&$request, $paymentMethod, $payment, $amount, $splitNumber)
    {
        $customDataHipay = Mage::helper('hipay')->getCustomData($payment, $amount, $paymentMethod, $splitNumber);

        // Add custom data for transaction request
        if (file_exists(Mage::getModuleDir('', 'Allopass_Hipay') . DS . 'Helper' . DS . 'CustomData.php')) {
            if (class_exists('Allopass_Hipay_Helper_CustomData', true)) {
                if (method_exists(Mage::helper('hipay/customData'), 'getCustomData')) {
                    $customData = Mage::helper('hipay/customData')->getCustomData($payment, $amount);
                    if (is_array($customData)) {
                        $customDataHipay = array_merge($customData, $customDataHipay);
                    }
                }
            }
        }

        $request->custom_data = json_encode(($customDataHipay));
    }

    protected function getRequestSource()
    {
        $source = array(
            "source" => "CMS",
            "brand" => "magento",
            "brand_version" => Mage::getVersion(),
            "integration_version" => (string)Mage::getConfig()->getNode('modules')->Allopass_Hipay->version
        );

        return json_encode($source);
    }

    protected function getBasket($operation = null)
    {
        $basket = Mage::getModel(
            $this->_cartFormatterClass,
            array("paymentMethod" => $this->_paymentMethod, "payment" => $this->_payment, "operation" => $operation)
        );

        return $basket->generate();
    }
}
