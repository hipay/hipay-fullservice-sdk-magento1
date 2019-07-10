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

use HiPay\Fullservice\Enum\Transaction\TransactionState;

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

        $order = $payment->getOrder();
        $urlAdmin = Mage::getUrl('adminhtml/sales_order/index');
        if (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/view')) {
            $urlAdmin = Mage::getUrl('adminhtml/sales_order/view', array('order_id' => $order->getId()));
        }

//        $this->_debug($gatewayResponse->debug());

        switch ($response->getState()) {
            case TransactionState::COMPLETED:
                return $this->isAdmin() ? $urlAdmin : Mage::helper('hipay')->getCheckoutSuccessPage($payment);
            case TransactionState::PENDING:
                $this->reAddToCart($order);
                return $this->isAdmin() ? $urlAdmin : Mage::getUrl($this->getConfigData('pending_redirect_page'));
            case TransactionState::FORWARDING:
                $payment->setIsTransactionPending(1);
                $order->save();
                return $response->getForwardUrl();
            case TransactionState::DECLINED:
                $this->reAddToCart($order);
                return $this->isAdmin() ? $urlAdmin : Mage::getUrl('checkout/onepage/failure');
            case TransactionState::ERROR:
            default:
                $this->reAddToCart($order);
                $this->_getCheckout()->setErrorMessage($this->getDefaultExceptionMessage());
                return $this->isAdmin() ? $urlAdmin : Mage::getUrl('checkout/onepage/failure');
        }
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
