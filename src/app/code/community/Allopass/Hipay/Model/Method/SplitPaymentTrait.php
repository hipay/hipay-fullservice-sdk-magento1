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

use HiPay\Fullservice\Enum\Transaction\ECI;
use HiPay\Fullservice\Gateway\Request\PaymentMethod\CardTokenPaymentMethod;

/**
 *
 * @author      HiPay <support.tpp@hipay.com>
 * @copyright   Copyright (c) 2019 - HiPay
 * @license     https://github.com/hipay/hipay-fullservice-sdk-magento1/blob/master/LICENSE.md
 * @link    https://github.com/hipay/hipay-fullservice-sdk-magento1
 */
trait Allopass_Hipay_Model_Method_SplitPaymentTrait
{

    public function paySplitPayment($splitPayment)
    {
        $order = Mage::getModel('sales/order')->load($splitPayment->getOrderId());

        if (!$order->getId()) {
            return null;
        }

        $payment = $order->getPayment();


        $request = Mage::getModel(
            'hipay/api_api',
            array(
                "paymentMethod" => $this,
                "payment" => $payment,
                "amount" => $splitPayment->getAmountToPay()
            )
        );

        return $request->requestDirectPost(
            $this->getCcTypeHipay($payment->getCcType()),
            $this->getSplitPaymentMethodFormatter($splitPayment),
            null,
            $this->getSplitAdditionalParameters($splitPayment, $order)
        );
    }

    public function getSplitPaymentMethodFormatter($splitPayment)
    {
        $cardTokenRequest = new CardTokenPaymentMethod();
        $cardTokenRequest->cardtoken = $splitPayment->getCardToken();
        $cardTokenRequest->eci = ECI::RECURRING_ECOMMERCE;
        $cardTokenRequest->authentication_indicator = 0;

        return $cardTokenRequest;
    }

    public function getSplitAdditionalParameters($splitPayment, $order)
    {
        return array(
            "splitPayment" => array(
                "orderid" => $order->getIncrementId() . $this->generateSplitOrderId($splitPayment),
                "description" => Mage::helper('hipay')->__(
                    "Order SPLIT %s by %s",
                    $order->getIncrementId(),
                    $order->getCustomerEmail()
                ),
                "operation" => "Sale",
                "splitNumber" => $splitPayment->getSplitNumber()
            )
        );
    }

    /**
     * Return an id with informations to TPP
     *
     * @param $splitPayment
     * @return string
     */
    public function generateSplitOrderId($splitPayment)
    {
        return "-split-" .
            $splitPayment->getSplitNumber() .
            "-" .
            $splitPayment->getAttempts() .
            "-" .
            $splitPayment->getId();
    }
}
