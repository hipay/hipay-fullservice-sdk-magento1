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
class Allopass_Hipay_Model_Api_Formatter_ThreeDS_PreviousAuthInfoFormatter implements Allopass_Hipay_Model_Api_Formatter_ApiFormatterInterface
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
     * @return \HiPay\Fullservice\Gateway\Model\Request\ThreeDSTwo\PreviousAuthInfo
     */
    public function generate()
    {
        $previousAuthInfo = new \HiPay\Fullservice\Gateway\Model\Request\ThreeDSTwo\PreviousAuthInfo();

        $this->mapRequest($previousAuthInfo);

        return $previousAuthInfo;
    }

    /**
     * @param \HiPay\Fullservice\Gateway\Model\Request\ThreeDSTwo\PreviousAuthInfo $previousAuthInfo
     */
    public function mapRequest(&$previousAuthInfo)
    {
        if(!$this->_order->getCustomerIsGuest()) {
            /**
             * @var Mage_Sales_Model_Resource_Order_Collection $orders
             */
            $orders = Mage::getResourceModel('sales/order_collection')
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
                ->addAttributeToSort('created_at', 'desc')
                ->load();

            foreach ($orders->getItems() as $order) {
                /**
                 * @var Mage_Sales_Model_Order $order
                 */
                if(!empty($order->getPayment()->getData('last_trans_id'))) {
                    $previousAuthInfo->transaction_reference = $order->getPayment()->getData('last_trans_id');
                    break;
                }
            }
        }
    }
}