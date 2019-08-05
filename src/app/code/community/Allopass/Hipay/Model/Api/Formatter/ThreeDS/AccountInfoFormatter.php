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
class Allopass_Hipay_Model_Api_Formatter_ThreeDS_AccountInfoFormatter implements Allopass_Hipay_Model_Api_Formatter_ApiFormatterInterface
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
     * @return \HiPay\Fullservice\Gateway\Model\Request\ThreeDSTwo\AccountInfo
     */
    public function generate()
    {
        $accountInfo = new \HiPay\Fullservice\Gateway\Model\Request\ThreeDSTwo\AccountInfo();

        $this->mapRequest($accountInfo);

        return $accountInfo;
    }

    /**
     * @param \HiPay\Fullservice\Gateway\Model\Request\ThreeDSTwo\AccountInfo $accountInfo
     */
    public function mapRequest(&$accountInfo)
    {
        $accountInfo->customer = $this->getCustomerInfo();
        $accountInfo->purchase = $this->getPurchaseInfo();
        $accountInfo->payment = $this->getPaymentInfo();
        $accountInfo->shipping = $this->getShippingInfo();
    }

    private function getCustomerInfo()
    {
        $info = new \HiPay\Fullservice\Gateway\Model\Request\ThreeDSTwo\AccountInfo\Customer();
        if(!$this->_order->getCustomerIsGuest()){
            $customerHelper = Mage::helper('customer/data');

            /**
             * @var Mage_Customer_Model_Customer $customer
             */
            $customer = $customerHelper->getCurrentCustomer();
            $creationDate = new DateTime('@'.$customer->getCreatedAtTimestamp());
            $updateDate = DateTime::createFromFormat('Y-m-d H:i:s', $customer->getUpdatedAt());

            $info->opening_account_date = $creationDate->format('Ymd');
            $info->account_change = $updateDate->format('Ymd');
        }

        return $info;
    }

    private function getPurchaseInfo()
    {
        $info = new \HiPay\Fullservice\Gateway\Model\Request\ThreeDSTwo\AccountInfo\Purchase();

        if(!$this->_order->getCustomerIsGuest()){
            $now = new \DateTime('now');
            $now = $now->format('Y-m-d H:i:s');
            $sixMonthAgo = new \DateTime('6 months ago');
            $sixMonthAgo = $sixMonthAgo->format('Y-m-d H:i:s');
            $twentyFourHoursAgo = new \DateTime('24 hours ago');
            $twentyFourHoursAgo = $twentyFourHoursAgo->format('Y-m-d H:i:s');
            $oneYearAgo = new \DateTime('1 years ago');
            $oneYearAgo = $oneYearAgo->format('Y-m-d H:i:s');

            $orders = Mage::getResourceModel('sales/order_collection')
                ->addAttributeToSelect('*')
                ->addAttributeToFilter(
                    'customer_id',
                    Mage::getSingleton('customer/session')->getCustomer()->getId()
                )
                ->addAttributeToFilter(
                    'state',
                    array('in' => Mage::getSingleton('sales/order_config')->getVisibleOnFrontStates())
                );

            $orders6Months = $orders
                ->addAttributeToFilter(
                    'created_at',
                    array('gt' => $sixMonthAgo)
                )
                ->addAttributeToSort('created_at', 'desc')
                ->load();

            $orders24Hours = $orders
                ->addAttributeToFilter(
                    'created_at',
                    array('gt' => $twentyFourHoursAgo)
                )
                ->addAttributeToSort('created_at', 'desc')
                ->load();

            $orders1Year = $orders
                ->addAttributeToFilter(
                    'created_at',
                    array('gt' => $oneYearAgo)
                )
                ->addAttributeToSort('created_at', 'desc')
                ->load();

            // Substracting 1 to remove current order from the count
            $info->count = $orders6Months->count() -1;
            $info->payment_attempts_1y = $orders1Year->count() -1;
            $info->payment_attempts_24h = $orders24Hours->count() -1;

            $info->card_stored_24h = 0;

            foreach($orders24Hours->getItems() as $order) {
                /**
                 * @var Mage_Sales_Model_Order $order
                 */
                if ($order->getId() != $this->_order->getId()) {
                    $payments = $order->getPaymentsCollection();

                    foreach ($payments->getItems() as $payment) {
                        /**
                         * @var Mage_Sales_Model_Order_Payment $payment
                         */
                        if ($payment->getData('additional_information')['create_oneclick']) {
                            $info->card_stored_24h++;
                        }

                    }
                }
            }
        }

        return $info;
    }

    private function getPaymentInfo()
    {
        $info = new \HiPay\Fullservice\Gateway\Model\Request\ThreeDSTwo\AccountInfo\Payment();

        if(!$this->_order->getCustomerIsGuest()) {

            $payments = $this->_order->getPaymentsCollection();
            $token = null;

            foreach($payments->getItems() as $payment){
                if($payment->getAdditionalInformation('use_oneclick')){
                    $token = $payment->getAdditionalInformation('token');
                }
            }

            if(!empty($token)) {
                $cards = Mage::getResourceModel('hipay/card_collection')
                    ->addFieldToSelect('*')
                    ->addFieldToFilter('customer_id', Mage::getSingleton('customer/session')->getCustomer()->getId())
                    ->addFieldToFilter('cc_status', Allopass_Hipay_Model_Card::STATUS_ENABLED)
                    ->addFieldToFilter('cc_token', $token)
                    ->setOrder('card_id', 'desc');

                foreach ($cards->getItems() as $card){
                    $info->enrollment_date = DateTime::createFromFormat("Y-m-d", $card->getCreatedAt())->format("Ymd");
                }
            }
        }

        return $info;
    }

    private function getShippingInfo()
    {
        $info = new \HiPay\Fullservice\Gateway\Model\Request\ThreeDSTwo\AccountInfo\Shipping();

        return $info;
    }

}