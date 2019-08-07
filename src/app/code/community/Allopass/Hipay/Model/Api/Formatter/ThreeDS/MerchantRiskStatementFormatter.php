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

use HiPay\Fullservice\Enum\ThreeDSTwo\DeliveryTimeFrame;
use HiPay\Fullservice\Enum\ThreeDSTwo\PurchaseIndicator;
use HiPay\Fullservice\Enum\ThreeDSTwo\ReorderIndicator;
use HiPay\Fullservice\Enum\ThreeDSTwo\ShippingIndicator;

/**
 * @author      HiPay <support.tpp@hipay.com>
 * @copyright   Copyright (c) 2019 - HiPay
 * @license     https://github.com/hipay/hipay-fullservice-sdk-magento1/blob/master/LICENSE.md
 * @link    https://github.com/hipay/hipay-fullservice-sdk-magento1
 */
class Allopass_Hipay_Model_Api_Formatter_ThreeDS_MerchantRiskStatementFormatter implements Allopass_Hipay_Model_Api_Formatter_ApiFormatterInterface
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
     * @return \HiPay\Fullservice\Gateway\Model\Request\ThreeDSTwo\MerchantRiskStatement
     */
    public function generate()
    {
        $merchantRiskStatement = new \HiPay\Fullservice\Gateway\Model\Request\ThreeDSTwo\MerchantRiskStatement();

        $this->mapRequest($merchantRiskStatement);

        return $merchantRiskStatement;
    }

    /**
     * @param \HiPay\Fullservice\Gateway\Model\Request\ThreeDSTwo\MerchantRiskStatement $merchantRiskStatement
     */
    public function mapRequest(&$merchantRiskStatement)
    {
        $merchantRiskStatement->email_delivery_address = $this->getEmailDeliveryAdress();
        $merchantRiskStatement->delivery_time_frame = !empty($merchantRiskStatement->email_delivery_address) ? DeliveryTimeFrame::ELECTRONIC_DELIVERY : DeliveryTimeFrame::OVERNIGHT_SHIPPING;
        $merchantRiskStatement->purchase_indicator = $this->getPurchaseIndicator();
        $merchantRiskStatement->pre_order_date = $this->getPreOrderDate();
        $merchantRiskStatement->reorder_indicator = $this->getReorderIndicator();
        $merchantRiskStatement->shipping_indicator = $this->getShippingIndicator();
    }

    private function getEmailDeliveryAdress(){
        foreach($this->_order->getAllItems() as $item){
            /**
             * @var Mage_Sales_Model_Order_Item $item
             */
            if($item->getIsVirtual()){
                return $this->_order->getCustomerEmail();
            }
        }

        return null;
    }

    private function getPurchaseIndicator()
    {
        foreach($this->_order->getAllItems() as $item){
            /**
             * @var Mage_Sales_Model_Order_Item $item
             */
            if($item->getStatusId() == Mage_Sales_Model_Order_Item::STATUS_BACKORDERED){
                return PurchaseIndicator::FUTURE_AVAILABILITY;
            }
        }

        return PurchaseIndicator::MERCHANDISE_AVAILABLE;
    }

    private function getPreOrderDate()
    {
        // TODO
        return null;
    }

    private function getReorderIndicator()
    {
        if(!$this->_order->getCustomerIsGuest()) {
            $currentItems = $this->_order->getAllItems();
            $orders = Mage::getResourceModel('sales/order_collection')
                ->addAttributeToSelect('*')
                ->addAttributeToFilter(
                    'customer_id',
                    Mage::getSingleton('customer/session')->getCustomer()->getId()
                )
                ->addAttributeToFilter(
                    'state',
                    array('in' => Mage::getSingleton('sales/order_config')->getVisibleOnFrontStates())
                )
                ->addAttributeToFilter(
                    'entity_id',
                    array('neq' => $this->_order->getId())
                )
                ->addAttributeToSort('created_at', 'desc')
                ->load();

            foreach ($orders->getItems() as $order) {
                /**
                 * @var Mage_Sales_Model_Order $order
                 */
                $comparingItems = $order->getAllItems();

                $found = false;

                foreach ($currentItems as $aCurrentItem) {
                    /**
                     * @var Mage_Sales_Model_Order_Item $aCurrentItem
                     */

                    $found = false;
                    foreach ($comparingItems as $compareKey => $aComparingItem) {
                        /**
                         * @var Mage_Sales_Model_Order_Item $aComparingItem
                         */
                        if ($aCurrentItem->getProductId() === $aComparingItem->getProductId()
                            && $aCurrentItem->getQtyOrdered() === $aComparingItem->getQtyOrdered()) {
                            $found = true;
                            unset($comparingItems[$compareKey]);
                            break;
                        }
                    }

                    if (!$found) {
                        break;
                    }
                }

                if ($found && count($comparingItems) == 0) {
                    return ReorderIndicator::REORDERED;
                }
            }
        }

        return ReorderIndicator::FIRST_TIME_ORDERED;

    }

    private function getShippingIndicator()
    {
        $onlyVirtual = true;
        foreach($this->_order->getAllItems() as $item){
            /**
             * @var Mage_Sales_Model_Order_Item $item
             */
            if(!$item->getIsVirtual()){
                $onlyVirtual = false;
                break;
            }
        }

        if($onlyVirtual){
            return ShippingIndicator::DIGITAL_GOODS;
        } else {
            if($this->_order->getShippingAddress()->getName() == $this->_order->getBillingAddress()->getName() &&
                $this->_order->getShippingAddress()->getCompany() == $this->_order->getBillingAddress()->getCompany() &&
                $this->_order->getShippingAddress()->getStreetFull() == $this->_order->getBillingAddress()->getStreetFull() &&
                $this->_order->getShippingAddress()->getCity() == $this->_order->getBillingAddress()->getCity() &&
                $this->_order->getShippingAddress()->getRegion() == $this->_order->getBillingAddress()->getRegion() &&
                $this->_order->getShippingAddress()->getPostcode() == $this->_order->getBillingAddress()->getPostcode() &&
                $this->_order->getShippingAddress()->getCountryId() == $this->_order->getBillingAddress()->getCountryId() &&
                $this->_order->getShippingAddress()->getTelephone() == $this->_order->getBillingAddress()->getTelephone() &&
                $this->_order->getShippingAddress()->getFax() == $this->_order->getBillingAddress()->getFax()){
                return ShippingIndicator::SHIP_TO_CARDHOLDER_BILLING_ADDRESS;
            } else {
                if(!$this->_order->getCustomerIsGuest()){
                    return ShippingIndicator::SHIP_TO_DIFFERENT_ADDRESS;
                } else {
                    $foundAddress = false;
                    $orders = Mage::getResourceModel('sales/order_collection')
                        ->addAttributeToSelect('*')
                        ->addAttributeToFilter(
                            'customer_id',
                            Mage::getSingleton('customer/session')->getCustomer()->getId()
                        )
                        ->addAttributeToFilter(
                            'state',
                            array('in' => Mage::getSingleton('sales/order_config')->getVisibleOnFrontStates())
                        )
                        ->addAttributeToFilter(
                            'entity_id',
                            array('neq' => $this->_order->getId())
                        )
                        ->addAttributeToSort('created_at', 'desc')
                        ->load();

                    foreach ($orders->getItems() as $order) {
                        /**
                         * @var Mage_Sales_Model_Order $order
                         */
                        if ($this->_order->getShippingAddress()->getName() == $order->getShippingAddress()->getName() &&
                            $this->_order->getShippingAddress()->getCompany() == $order->getShippingAddress()->getCompany() &&
                            $this->_order->getShippingAddress()->getStreetFull() == $order->getShippingAddress()->getStreetFull() &&
                            $this->_order->getShippingAddress()->getCity() == $order->getShippingAddress()->getCity() &&
                            $this->_order->getShippingAddress()->getRegion() == $order->getShippingAddress()->getRegion() &&
                            $this->_order->getShippingAddress()->getPostcode() == $order->getShippingAddress()->getPostcode() &&
                            $this->_order->getShippingAddress()->getCountryId() == $order->getShippingAddress()->getCountryId() &&
                            $this->_order->getShippingAddress()->getTelephone() == $order->getShippingAddress()->getTelephone() &&
                            $this->_order->getShippingAddress()->getFax() == $order->getShippingAddress()->getFax()) {
                            return ShippingIndicator::SHIP_TO_VERIFIED_ADDRESS;
                        }
                    }
                }
            }
        }

        return ShippingIndicator::SHIP_TO_DIFFERENT_ADDRESS;
    }
}