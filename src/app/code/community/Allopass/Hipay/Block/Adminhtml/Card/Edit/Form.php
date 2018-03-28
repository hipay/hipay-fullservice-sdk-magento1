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
class Allopass_Hipay_Block_Adminhtml_Card_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{

    protected function _prepareForm()
    {

        /* @var $card Allopass_Hipay_Model_Card */
        $card = Mage::registry('current_card');


        $form = new Varien_Data_Form(
            array('id' => 'edit_form', 'action' => $this->getUrl('*/card/save'), 'method' => 'post')
        );

        $fieldset = $form->addFieldset('card_form', array('legend' => Mage::helper('hipay')->__('Card Hipay')));

        if ($card->getCardId()) {
            $fieldset->addField(
                'card_id',
                'hidden',
                array(
                    'name' => 'card_id',
                )
            );
            $fieldset->addField(
                'customer_id',
                'hidden',
                array(
                    'name' => 'customer_id',
                )
            );
        }
        $fieldset->addField(
            'name',
            'text',
            array(
                'label' => Mage::helper('hipay')->__('Card #'),
                'title' => Mage::helper('hipay')->__('Card #'),
                'class' => 'required-entry',
                'required' => true,
                'name' => 'name',
            )
        );

        $fieldset->addField(
            'is_default',
            'select',
            array(
                'label' => Mage::helper('hipay')->__('Is default'),
                'title' => Mage::helper('hipay')->__('Is default'),
                'name' => 'is_default',
                'values' => array(Mage::helper('sales')->__('No'), Mage::helper('adminhtml')->__('Yes'))
            )
        );

        $statues = array(
            Allopass_Hipay_Model_Card::STATUS_ENABLED => $this->__('Enabled'),
            Allopass_Hipay_Model_Card::STATUS_DISABLED => $this->__('Disabled')
        );
        $fieldset->addField(
            'cc_status',
            'select',
            array(
                'label' => Mage::helper('hipay')->__('Status'),
                'title' => Mage::helper('hipay')->__('Status'),
                'name' => 'cc_status',
                'values' => $statues
            )
        );


        $fieldset_info = $form->addFieldset('card_info', array('legend' => Mage::helper('hipay')->__('Informations')));

        $fieldset_info->addField(
            'cc_type',
            'text',
            array(
                'label' => Mage::helper('payment')->__('Card type'),
                'title' => Mage::helper('payment')->__('Card type'),
                'name' => 'cc_type',
                'readonly' => true,
            )
        );

        $fieldset_info->addField(
            'cc_number_enc',
            'text',
            array(
                'label' => Mage::helper('payment')->__('Card number'),
                'title' => Mage::helper('payment')->__('Card number'),
                'name' => 'cc_number_enc',
                'readonly' => true,
            )
        );

        $fieldset_info->addField(
            'cc_exp_month',
            'text',
            array(
                'label' => Mage::helper('payment')->__('Card Exp. month'),
                'title' => Mage::helper('payment')->__('Card Exp. month'),
                'name' => 'cc_exp_month',
                'readonly' => true,
            )
        );

        $fieldset_info->addField(
            'cc_exp_year',
            'text',
            array(
                'label' => Mage::helper('payment')->__('Card Exp. year'),
                'title' => Mage::helper('payment')->__('Card Exp. year'),
                'name' => 'cc_exp_year',
                'readonly' => true,
            )
        );


        $fieldset_info->addField(
            'cc_token',
            'text',
            array(
                'label' => Mage::helper('hipay')->__('Card Token'),
                'title' => Mage::helper('hipay')->__('Card Token'),
                'readonly' => true,
                'name' => 'cc_token',
            )
        );


        $form->setUseContainer(true);
        $form->setValues($card->getData());
        $this->setForm($form);
        return parent::_prepareForm();
    }

}
