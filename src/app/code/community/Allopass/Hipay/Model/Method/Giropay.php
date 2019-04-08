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
class Allopass_Hipay_Model_Method_Giropay extends Allopass_Hipay_Model_Method_Hosted
{
    protected $_code = 'hipay_giropay';

    protected $_canCapture = false;
    protected $_canCapturePartial = false;
    protected $_canRefund = false;
    protected $_canRefundInvoicePartial = false;

    public function place($payment, $amount)
    {
        $order = $payment->getOrder();
        $customer = Mage::getModel('customer/customer')->load($order->getCustomerId());

        $request = Mage::getModel('hipay/api_request', array($this));

        $payment->setAmount($amount);

        $token = null;
        if ($payment->getAdditionalInformation('use_oneclick')) {
            $cardId = $payment->getAdditionalInformation('selected_oneclick_card');
            $card = Mage::getModel('hipay/card')->load($cardId);

            if ($card->getId() && $card->getCustomerId() == $customer->getId()) {
                $token = $card->getCcToken();
            } else {
                Mage::throwException(Mage::helper('hipay')->__("Error with your card!"));
            }
        }

        $gatewayParams = $this->getGatewayParams($payment, $amount, $token);

        if ($token === null) {
            $gatewayParams['payment_product'] = 'cb';
            $gatewayParams['operation'] = $this->getOperation();
            $gatewayParams['css'] = $this->getConfigData('css_url');
            $gatewayParams['template'] = $this->getConfigData('display_iframe') ? 'iframe' : $this->getConfigData(
                'template'
            );
            if ($this->getConfigData('template') == 'basic-js' && $gatewayParams['template'] == 'iframe') {
                $gatewayParams['template'] .= '-js';
            }

            $gatewayParams['display_selector'] = $this->getConfigData('display_selector');

            if ($gatewayParams['country'] == 'BE') {
                $gatewayParams['payment_product_list'] = $this->getConfigData('cctypes');
            } else {
                $gatewayParams['payment_product_list'] = str_replace('bcmc', '', $this->getConfigData('cctypes'));
            }


            $gatewayParams['payment_product_category_list'] = "credit-card";

            if (Mage::getStoreConfig('general/store_information/name') != "") {
                $gatewayParams['merchant_display_name'] = Mage::getStoreConfig('general/store_information/name');
            }

            $gatewayParams['description'] = substr($gatewayParams['description'], 0, 30);

            $this->_debug($gatewayParams);

            $gatewayResponse = $request->gatewayRequest(
                Allopass_Hipay_Model_Api_Request::GATEWAY_ACTION_HOSTED,
                $gatewayParams,
                $payment->getOrder()->getStoreId()
            );

            $this->_debug($gatewayResponse->debug());

            return $gatewayResponse->getForwardUrl();
        } else {
            $gatewayParams['operation'] = $this->getOperation();
            $gatewayParams['payment_product'] = Mage::getSingleton('customer/session')->getCustomer()->getHipayCcType();

            $gatewayParams['description'] = substr($gatewayParams['description'], 0, 30);

            $this->_debug($gatewayParams);

            $gatewayResponse = $request->gatewayRequest(
                Allopass_Hipay_Model_Api_Request::GATEWAY_ACTION_ORDER,
                $gatewayParams,
                $payment->getOrder()->getStoreId()
            );

            $this->_debug($gatewayResponse->debug());

            $redirectUrl = $this->processResponseToRedirect($gatewayResponse, $payment, $amount);

            return $redirectUrl;
        }

    }
}
