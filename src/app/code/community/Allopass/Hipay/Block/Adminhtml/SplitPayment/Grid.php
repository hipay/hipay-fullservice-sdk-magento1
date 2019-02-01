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
class Allopass_Hipay_Block_Adminhtml_SplitPayment_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    protected $_defaultSort = 'split_payment_id';

    protected function _prepareCollection()
    {

        $collection = Mage::getModel('hipay/splitPayment')->getCollection();
        $this->setCollection($collection);
        parent::_prepareCollection();
        return $this;
    }


    protected function _prepareColumns()
    {


        $this->addColumn(
            'split_payment_id',
            array(
                'header' => Mage::helper('hipay')->__('ID'),
                'width' => '50px',
                'type' => 'number',
                'index' => 'split_payment_id',
            )
        );
        $this->addColumn(
            'real_order_id',
            array(
                'header' => Mage::helper('sales')->__('Order #'),
                'type' => 'text',
                'width' => '20px',
                'index' => 'real_order_id',
            )
        );

        $this->addColumn(
            'customer_id',
            array(
                'header' => Mage::helper('customer')->__('Customer ID'),
                'type' => 'text',
                'width' => '20px',
                'index' => 'customer_id',
            )
        );

        $this->addColumn(
            'card_token',
            array(
                'header' => Mage::helper('hipay')->__('Card Token'),
                'type' => 'text',
                'width' => '60px',
                'index' => 'card_token',
            )
        );


        $this->addColumn(
            'total_amount',
            array(
                'header' => Mage::helper('sales')->__('G.T. (Purchased)'),
                'index' => 'total_amount',
                'type' => 'currency',
            )
        );


        $this->addColumn(
            'amount_to_pay',
            array(
                'header' => Mage::helper('hipay')->__('Amount to pay'),
                'type' => 'currency',
                'index' => 'amount_to_pay',
            )
        );

        $this->addColumn(
            'date_to_pay',
            array(
                'header' => Mage::helper('hipay')->__('Date to pay'),
                'type' => 'date',
                'index' => 'date_to_pay',
            )
        );

        $this->addColumn(
            'attempts',
            array(
                'header' => Mage::helper('hipay')->__('Attempts'),
                'index' => 'attempts',
                'type' => 'number',
            )
        );

        $this->addColumn(
            'status',
            array(
                'header' => Mage::helper('hipay')->__('Status'),
                'index' => 'status',
                'type' => 'options',
                'options' => Allopass_Hipay_Model_SplitPayment::getStatues(),
            )
        );


        return parent::_prepareColumns();
    }

    /**
     * Row click url
     *
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('split_payment_id' => $row->getId()));
    }
}
