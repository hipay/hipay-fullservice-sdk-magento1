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
trait Allopass_Hipay_Model_Method_OneClickTrait
{

    public function payOneClick($payment, $amount)
    {
        $order = $payment->getOrder();
        $customer = Mage::getModel('customer/customer')->load($order->getCustomerId());

        $cardId = $payment->getAdditionalInformation('selected_oneclick_card');
        $card = Mage::getModel('hipay/card')->load($cardId);

        if (!$card->getId() || $card->getCustomerId() !== $customer->getId()) {
            Mage::throwException(Mage::helper('hipay')->__("Error with your card!"));
        }

        $request = Mage::getModel(
            'hipay/api_api',
            array(
                "paymentMethod" => $this,
                "payment" => $payment,
                "amount" => $amount
            )
        );

        $response = $request->requestDirectPost(
            $this->getCcTypeHipay($card->getCcType()),
            $this->getOneClickMethodFormatter($payment, $card),
            $payment->getAdditionalInformation('device_fingerprint'),
            null
        );

        return $this->handleApiResponse($response, $payment);
    }

    public function getOneClickMethodFormatter($payment, $card)
    {
        $cardTokenRequest = new CardTokenPaymentMethod();

        $cardTokenRequest->cardtoken = $card->getCcToken();
        $cardTokenRequest->eci = ECI::RECURRING_ECOMMERCE;
        $cardTokenRequest->authentication_indicator = Mage::helper('hipay')->is3dSecure(
            $this->getConfigData('use_3d_secure'),
            $this->getConfigData('config_3ds_rules'),
            $payment
        );

        return $cardTokenRequest;
    }
}
