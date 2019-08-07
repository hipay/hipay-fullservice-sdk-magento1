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

use HiPay\Fullservice\Enum\ThreeDSTwo\DeviceChannel;
use HiPay\Fullservice\Enum\Transaction\ECI;
require_once(dirname(__FILE__) . '/../../../../Helper/Enum/CardPaymentProduct.php');

/**
 * @author      HiPay <support.tpp@hipay.com>
 * @copyright   Copyright (c) 2018 - HiPay
 * @license     https://github.com/hipay/hipay-fullservice-sdk-magento1/blob/master/LICENSE.md
 * @link    https://github.com/hipay/hipay-fullservice-sdk-magento1
 */
abstract class Allopass_Hipay_Model_Api_Formatter_Request_OrderRequestAbstract extends Allopass_Hipay_Model_Api_Formatter_ApiFormatterAbstract
{

    protected $_cartFormatterClass = 'hipay/api_formatter_cart_cartOrderFormatter';

    protected $_splitData;
    protected $_splitNumber;
    protected $_additionalParameters;

    public function __construct($args)
    {
        parent::__construct($args);

        $this->_splitData = (isset($args["additionalParameters"]["splitPayment"])) ? $args["additionalParameters"]["splitPayment"] : null;
        $this->_splitNumber = ($this->_splitData !== null) ? $this->_splitData["splitNumber"] : null;
        $this->_additionalParameters = $args["additionalParameters"];
    }

    /**
     * @param HiPay\Fullservice\Gateway\Request\Order\OrderRequest $orderRequest
     * @return mixed|void
     * @throws Mage_Core_Model_Store_Exception
     */
    public function mapRequest(&$orderRequest)
    {
        parent::mapRequest($orderRequest);

        $this->setCustomData(
            $orderRequest,
            $this->_paymentMethod,
            $this->_payment,
            $this->_amount,
            $this->_splitNumber
        );

        if (in_array(strtolower($this->_paymentMethod->getCode()), CardPaymentProduct::threeDS2Available)) {
            $orderRequest->browser_info = $this->getBrowserInfo();
            $orderRequest->previous_auth_info = $this->getPreviousAuthInfo();
            $orderRequest->merchant_risk_statement = $this->getMerchantRiskStatement();
            $orderRequest->account_info = $this->getAccountInfo();
            $orderRequest->recurring_info = $this->getRecurringInfo();

            // If split payment exists, it means we are at least on the second payment of the split
            $_helper = Mage::helper('hipay');
            if($_helper->splitPaymentsExists($this->_payment->getOrder()->getId())) {
                $orderRequest->device_channel = DeviceChannel::THREE_DS_REQUESTOR_INITIATED;
            } else {
                $orderRequest->device_channel = DeviceChannel::BROWSER;
            }
        }

        Mage::dispatchEvent('hipay_order_before_request', array("OrderRequest" => &$orderRequest, "Cart" => $this->_payment->getOrder()->getAllItems()));

        $orderRequest->orderid = $this->_payment->getOrder()->getIncrementId();

        if ($this->_paymentMethod->getConfigData('payment_action') !==
            Mage_Payment_Model_Method_Abstract::ACTION_AUTHORIZE
        ) {
            $orderRequest->operation = "Sale";
        } else {
            $orderRequest->operation = "Authorization";
        }

        $orderRequest->description = Mage::helper('hipay')->__(
            "Order %s by %s",
            $this->_payment->getOrder()->getIncrementId(),
            $this->_payment->getOrder()->getCustomerEmail()
        );

        $orderRequest->amount = $this->_amount;
        $orderRequest->tax = $this->_payment->getOrder()->getTaxAmount();
        $orderRequest->tax_rate = Mage::helper('hipay')->getTaxeRateInformation($this->_payment->getOrder());

        $this->initSplitPayment($orderRequest);

        $orderRequest->shipping = $this->_payment->getOrder()->getShippingAmount();

        if ($this->isUseOrderCurrency()) {
            $orderRequest->currency = $this->_payment->getOrder()->getOrderCurrencyCode();
        } else {
            $orderRequest->currency = $this->_payment->getOrder()->getBaseCurrencyCode();
        }

        $orderRequest->cid = $this->_payment->getOrder()->getCustomerId();

        if ($this->isMoto()) {
            $acceptUrl = Mage::getUrl(
                $this->_paymentMethod->getConfigData('accept_url'),
                array("order" => $orderRequest->orderid)
            );

            $declineUrl = Mage::getUrl(
                $this->_paymentMethod->getConfigData('decline_url'),
                array("order" => $orderRequest->orderid)
            );
            $pendingUrl = Mage::getUrl(
                $this->_paymentMethod->getConfigData('pending_url'),
                array("order" => $orderRequest->orderid)
            );
            $exceptionUrl = Mage::getUrl(
                $this->_paymentMethod->getConfigData('exception_url'),
                array("order" => $orderRequest->orderid)
            );
            $cancelUrl = Mage::getUrl(
                $this->_paymentMethod->getConfigData('cancel_url'),
                array("order" => $orderRequest->orderid)
            );
        } else {
            $acceptUrl = $this->isAdmin() ? Mage::helper('adminhtml')->getUrl('*/payment/accept') :
                Mage::getUrl($this->_paymentMethod->getConfigData('accept_url'));

            $declineUrl = $this->isAdmin() ? Mage::helper('adminhtml')->getUrl('*/payment/decline') :
                Mage::getUrl($this->_paymentMethod->getConfigData('decline_url'));

            $pendingUrl = $this->isAdmin() ? Mage::helper('adminhtml')->getUrl('*/payment/pending') :
                Mage::getUrl($this->_paymentMethod->getConfigData('pending_url'));

            $exceptionUrl = $this->isAdmin() ? Mage::helper('adminhtml')->getUrl('*/payment/exception') :
                Mage::getUrl($this->_paymentMethod->getConfigData('exception_url'));

            $cancelUrl = $this->isAdmin() ? Mage::helper('adminhtml')->getUrl('*/payment/cancel') :
                Mage::getUrl($this->_paymentMethod->getConfigData('cancel_url'));
        }

        $orderRequest->accept_url = $acceptUrl;
        $orderRequest->decline_url = $declineUrl;
        $orderRequest->pending_url = $pendingUrl;
        $orderRequest->exception_url = $exceptionUrl;
        $orderRequest->cancel_url = $cancelUrl;

        if ($this->_paymentMethod->getConfig("send_notification_url", Mage::app()->getStore()->getId())) {
            $orderRequest->notify_url = Mage::getUrl("hipay/notify/index");
        }

        $orderRequest->customerBillingInfo = $this->getCustomerBillingInfo();

        if (!$this->_payment->getOrder()->getIsVirtual()) {
            $orderRequest->customerShippingInfo = $this->getCustomerShippingInfo();
        }

        $orderRequest->ipaddr = $this->getIpAddress();
        $orderRequest->language = Mage::app()->getLocale()->getLocaleCode();
        $orderRequest->http_user_agent = Mage::helper('core/http')->getHttpUserAgent();
        $orderRequest->http_accept = "*/*";

        if (Mage::helper('hipay')->isSendCartItemsRequired($this->_payment->getCcType())) {
            $orderRequest->basket = $this->getBasket();
        }

        if (Mage::helper('hipay')->isDeliveryMethodAndCartItemsRequired($this->_payment->getCcType())) {
            if ($this->_payment->getOrder()->getShippingMethod() && !$this->_payment->getOrder()->getIsVirtual()) {
                $orderRequest->delivery_information = $this->getDeliveryInformation();
            }
        }

        if (isset($this->_additionalParameters["payment_product_parameters"])) {
            $orderRequest->payment_product_parameters = $this->_additionalParameters["payment_product_parameters"];
        }

        if (isset($this->_additionalParameters["isMoto"]) && $this->_additionalParameters["isMoto"]) {
            // for hosted page MO/TO transaction
            $orderRequest->eci = ECI::MOTO;
        }
    }

    protected function initSplitPayment(&$orderRequest)
    {
        $firstPaymentsSplit = $this->getSplitPayment($orderRequest->tax);

        if ($firstPaymentsSplit && $this->_splitData === null) {
            $orderRequest->long_description = Mage::helper('hipay')->__('Split payment');
            $orderRequest->amount = $firstPaymentsSplit[0]['amountToPay'];
            $orderRequest->tax = $firstPaymentsSplit[0]['taxAmountToPay'];
        } elseif ($this->_splitData !== null) {
            $orderRequest->orderid = $this->_splitData["orderid"];
            $orderRequest->description = $this->_splitData["description"];
            $orderRequest->operation = $this->_splitData["operation"];
        }
    }

    protected function getDeliveryInformation()
    {
        $deliveryInformation = Mage::getModel(
            'hipay/api_formatter_cart_deliveryInformationFormatter',
            array("paymentMethod" => $this->_paymentMethod, "payment" => $this->_payment)
        );

        return $deliveryInformation->generate();
    }

    /**
     * Return mapped customer billing information
     *
     * @return \HiPay\Fullservice\Gateway\Request\Info\CustomerBillingInfoRequest|mixed
     * @throws Hipay_Payment_Exception
     */
    protected function getCustomerBillingInfo()
    {

        $billingInfo = Mage::getModel(
            'hipay/api_formatter_info_customerBillingInfoFormatter',
            array("paymentMethod" => $this->_paymentMethod, "payment" => $this->_payment)
        );

        return $billingInfo->generate();
    }

    /**
     * return mapped customer shipping information
     *
     * @return \HiPay\Fullservice\Gateway\Request\Info\CustomerShippingInfoRequest
     */
    protected function getCustomerShippingInfo()
    {
        $customerShippingInfo = Mage::getModel(
            'hipay/api_formatter_info_customerShippingInfoFormatter',
            array("paymentMethod" => $this->_paymentMethod, "payment" => $this->_payment)
        );

        return $customerShippingInfo->generate();
    }

    protected function getSplitPayment($taxAmount)
    {
        $profile = $this->_payment->getAdditionalInformation('split_payment_id');

        if (!$profile) {
            return false;
        }

        $spCollection = Mage::getModel('hipay/splitPayment')
                            ->getCollection()
                            ->addFieldToFilter('order_id', $this->_payment->getOrder()->getId());

        if (!$spCollection->count()) {
            return Mage::Helper('hipay')->splitPayment((int)$profile, $this->_amount, $taxAmount);
        }

        return false;
    }

    protected function isUseOrderCurrency()
    {
        return Mage::getStoreConfig('hipay/hipay_api/currency_transaction', Mage::app()->getStore()->getId());
    }

    protected function isMoto()
    {
        return Mage::getStoreConfig('hipay/hipay_api_moto/moto_send_email', Mage::app()->getStore())
            && $this->_paymentMethod->getCode() === 'hipay_hostedmoto';
    }

    protected function isAdmin()
    {
        return Mage::app()->getStore()->isAdmin();
    }

    protected function getIpAddress()
    {
        $remoteIp = $this->_payment->getOrder()->getRemoteIp();

        //Check if it's forwarded and in this case, explode and retrieve the first part
        if ($this->_payment->getOrder()->getXForwardedFor() !== null) {
            if (strpos($this->_payment->getOrder()->getXForwardedFor(), ",") !== false) {
                $xfParts = explode(",", $this->_payment->getOrder()->getXForwardedFor());
                $remoteIp = current($xfParts);
            } else {
                $remoteIp = $this->_payment->getOrder()->getXForwardedFor();
            }
        }

        return $remoteIp;
    }

    private function getBrowserInfo()
    {
        $browserInfo = Mage::getModel(
            'hipay/api_formatter_threeDS_browserInfoFormatter',
            array("paymentMethod" => $this->_paymentMethod, "payment" => $this->_payment)
        );

        return $browserInfo->generate();
    }

    private function getPreviousAuthInfo()
    {
        $previousAuthInfo = Mage::getModel(
            'hipay/api_formatter_threeDS_previousAuthInfoFormatter',
            array("paymentMethod" => $this->_paymentMethod, "payment" => $this->_payment)
        );

        return $previousAuthInfo->generate();
    }

    private function getMerchantRiskStatement()
    {
        $merchantRiskStatement = Mage::getModel(
            'hipay/api_formatter_threeDS_merchantRiskStatementFormatter',
            array("paymentMethod" => $this->_paymentMethod, "payment" => $this->_payment)
        );

        return $merchantRiskStatement->generate();
    }

    private function getAccountInfo()
    {
        $accountInfo = Mage::getModel(
            'hipay/api_formatter_threeDS_accountInfoFormatter',
            array("paymentMethod" => $this->_paymentMethod, "payment" => $this->_payment)
        );

        return $accountInfo->generate();
    }

    private function getRecurringInfo()
    {
        $recurringInfo = Mage::getModel(
            'hipay/api_formatter_threeDS_recurringInfoFormatter',
            array("paymentMethod" => $this->_paymentMethod, "payment" => $this->_payment)
        );

        return $recurringInfo->generate();
    }
}
