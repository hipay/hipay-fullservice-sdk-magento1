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

use \HiPay\Fullservice\Enum\ThreeDSTwo\NameIndicator;

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
            /**
             * @var Mage_Customer_Model_Customer $customer
             */
            $customer = new Mage_Customer_Model_Customer();
            $customer->load($this->_order->getCustomerId());
            $creationDate = new DateTime('@'.$customer->getCreatedAtTimestamp());
            $updateDate = DateTime::createFromFormat('Y-m-d H:i:s', $customer->getUpdatedAt());

            $info->opening_account_date = (int)($creationDate->format('Ymd'));
            $info->account_change = (int)($updateDate->format('Ymd'));
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

            $orders6Months = $this->getOrdersSince($sixMonthAgo);
            $orders24Hours = $this->getOrdersSince($twentyFourHoursAgo);
            $orders1Year = $this->getOrdersSince($oneYearAgo);

            // Substracting 1 to remove current order from the count
            $info->count = (int)($orders6Months->count() -1);
            $info->payment_attempts_1y = (int)($orders1Year->count() -1);
            $info->payment_attempts_24h = (int)($orders24Hours->count() -1);

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
                        if ($payment->getAdditionalInformation('create_oneclick')) {
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
                    ->addFieldToFilter('customer_id', $this->_order->getCustomerId())
                    ->addFieldToFilter('cc_status', Allopass_Hipay_Model_Card::STATUS_ENABLED)
                    ->addFieldToFilter('cc_token', $token)
                    ->setOrder('card_id', 'desc');

                foreach ($cards->getItems() as $card){
                    if(!empty($card->getCreatedAt())) {
                        $info->enrollment_date = (int)(DateTime::createFromFormat("Y-m-d", $card->getCreatedAt())->format("Ymd"));
                    }
                }
            }
        }

        return $info;
    }

    private function getShippingInfo()
    {
        $info = new \HiPay\Fullservice\Gateway\Model\Request\ThreeDSTwo\AccountInfo\Shipping();

        if(!$this->_order->getCustomerIsGuest() && $this->_order->getShippingAddress()) {
            $shippingAddress = $this->_order->getShippingAddress();

            $allOrders = Mage::getResourceModel('sales/order_collection')
                ->addAttributeToSelect('*')
                ->addAttributeToFilter(
                    'customer_id',
                    $this->_order->getCustomerId()
                )
                ->addAttributeToFilter(
                    'state',
                    array('in' => Mage::getSingleton('sales/order_config')->getVisibleOnFrontStates())
                )
                ->addAttributeToFilter(
                    'entity_id',
                    array('neq' => $this->_order->getId())
                )
                ->addAttributeToSort('created_at', 'asc')
                ->load();

            foreach($allOrders->getItems() as $order) {
                /**
                 * @var Mage_Sales_Model_Order $order
                 */
                if ($order->getShippingAddress() &&
                    $this->_order->getShippingAddress()->getName() == $order->getShippingAddress()->getName() &&
                    $this->_order->getShippingAddress()->getCompany() == $order->getShippingAddress()->getCompany() &&
                    $this->_order->getShippingAddress()->getStreetFull() == $order->getShippingAddress()->getStreetFull() &&
                    $this->_order->getShippingAddress()->getCity() == $order->getShippingAddress()->getCity() &&
                    $this->_order->getShippingAddress()->getRegion() == $order->getShippingAddress()->getRegion() &&
                    $this->_order->getShippingAddress()->getPostcode() == $order->getShippingAddress()->getPostcode() &&
                    $this->_order->getShippingAddress()->getCountryId() == $order->getShippingAddress()->getCountryId() &&
                    $this->_order->getShippingAddress()->getTelephone() == $order->getShippingAddress()->getTelephone() &&
                    $this->_order->getShippingAddress()->getFax() == $order->getShippingAddress()->getFax()
                ) {
                    $info->shipping_used_date = (int)(DateTime::createFromFormat('Y-m-d H:i:s', $order->getCreatedAt())->format('Ymd'));
                    break;
                }
            }

            /**
             * @var Mage_Customer_Model_Customer $customer
             */
            $customer = new Mage_Customer_Model_Customer();
            $customer->load($this->_order->getCustomerId());

            if(!$shippingAddress ||
                empty($shippingAddress->getName()) ||
                empty($customer->getName()) ||
                strtoupper($customer->getName()) == strtoupper($shippingAddress->getName())
            ){
                $info->name_indicator = NameIndicator::IDENTICAL;
            } else {
                $info->name_indicator = NameIndicator::DIFFERENT;
            }
        }

        return $info;
    }

    /**
     * @param string $sinceDate
     * @return Mage_Sales_Model_Resource_Order_Collection
     */
    private function getOrdersSince(string $sinceDate)
    {
        return Mage::getResourceModel('sales/order_collection')
            ->addAttributeToSelect('*')
            ->addAttributeToFilter(
                'customer_id',
                $this->_order->getCustomerId()
            )
            ->addAttributeToFilter(
                'state',
                array('in' => Mage::getSingleton('sales/order_config')->getVisibleOnFrontStates())
            )
            ->addAttributeToFilter(
                'created_at',
                array('gt' => $sinceDate)
            )
            ->addAttributeToSort('created_at', 'desc')
            ->load()
        ;
    }
}
