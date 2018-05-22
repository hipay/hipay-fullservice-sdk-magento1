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
class Allopass_Hipay_Block_Adminhtml_SplitPayment_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{

    protected function _prepareForm()
    {
        /* @var $profile Allopass_Hipay_Model_SplitPayment */
        $splitPayment = Mage::registry('split_payment');

        $form = new Varien_Data_Form(
            array('id' => 'edit_form', 'action' => $this->getUrl('*/splitPayment/save'), 'method' => 'post')
        );

        $fieldset = $form->addFieldset(
            'splitPayment_form',
            array('legend' => Mage::helper('hipay')->__('Split Payment'))
        );

        if ($splitPayment->getSplitPaymentId()) {
            $fieldset->addField(
                'split_payment_id',
                'hidden',
                array(
                    'name' => 'split_payment_id',
                )
            );
        }

        $fieldset->addField(
            'real_order_id',
            'text',
            array(
                'label' => Mage::helper('sales')->__('Order #'),
                'title' => Mage::helper('sales')->__('Order #'),
                'class' => 'required-entry',
                'required' => true,
                'name' => 'real_order_id',
                'readonly' => true,
            )
        );

        $fieldset->addField(
            'customer_id',
            'text',
            array(
                'label' => Mage::helper('customer')->__('Customer ID'),
                'title' => Mage::helper('customer')->__('Customer ID'),
                'class' => 'required-entry',
                'required' => true,
                'name' => 'customer_id',
                'readonly' => true,
            )
        );

        $fieldset->addField(
            'card_token',
            'text',
            array(
                'label' => Mage::helper('hipay')->__('Card Token'),
                'title' => Mage::helper('hipay')->__('Card Token'),
                'class' => 'required-entry',
                'required' => true,
                'name' => 'card_token',
            )
        );

        $fieldset->addField(
            'total_amount',
            'text',
            array(
                'label' => Mage::helper('sales')->__('G.T. (Purchased)'),
                'title' => Mage::helper('sales')->__('G.T. (Purchased)'),
                'class' => 'required-entry validate-number',
                'required' => true,
                'name' => 'total_amount',
                'readonly' => true,
            )
        );

        $fieldset->addField(
            'amount_to_pay',
            'text',
            array(
                'label' => Mage::helper('hipay')->__('Amount to pay'),
                'title' => Mage::helper('hipay')->__('Amount to pay'),
                'class' => 'required-entry validate-number',
                'required' => true,
                'name' => 'amount_to_pay',
            )
        );

        $fieldset->addField(
            'date_to_pay',
            'date',
            array(
                'label' => Mage::helper('hipay')->__('Date to pay'),
                'title' => Mage::helper('hipay')->__('Date to pay'),
                'class' => 'required-entry',
                'required' => true,
                'name' => 'date_to_pay',
                'format' => Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT),
                'image' => $this->getSkinUrl('images/grid-cal.gif'),
            )
        );

        $fieldset->addField(
            'attempts',
            'text',
            array(
                'label' => Mage::helper('hipay')->__('Attempts'),
                'title' => Mage::helper('hipay')->__('Attempts'),
                'class' => 'required-entry validate-number',
                'required' => true,
                'name' => 'attempts',
                'readonly' => true,
            )
        );

        $fieldset->addField(
            'status',
            'select',
            array(
                'label' => Mage::helper('hipay')->__('Status'),
                'title' => Mage::helper('hipay')->__('Status'),
                'name' => 'status',
                'values' => Allopass_Hipay_Model_SplitPayment::getStatues(),
            )
        );

        $fieldset->addField(
            'split_number',
            'text',
            array(
                'label' => Mage::helper('hipay')->__('Split number'),
                'title' => Mage::helper('hipay')->__('Split number'),
                'required' => false,
                'name' => 'split_number',
                'readonly' => false,
            )
        );


        $form->setUseContainer(true);
        $form->setValues($splitPayment->getData());
        $this->setForm($form);
        return parent::_prepareForm();
    }

}
