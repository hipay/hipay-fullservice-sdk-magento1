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
class Allopass_Hipay_Block_Adminhtml_SplitPayment_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    /**
     * Initialize cms page edit block
     * Allopass_Hipay_Block_Adminhtml_SplitPayment_Edit constructor.
     */
    public function __construct()
    {

        $this->_objectId = 'split_payment_id';
        $this->_blockGroup = 'hipay';
        $this->_controller = 'adminhtml_splitPayment';
        $this->_headerText = $this->__('Split Payment');
        parent::__construct();

        $this->removeButton('delete');


        $this->_addButton(
            'saveandcontinue',
            array(
                'label' => Mage::helper('adminhtml')->__('Save and Continue Edit'),
                'onclick' =>
                    'saveAndContinueEdit(\''
                    . $this->getUrl('*/*/save', array('_current' => true, 'back' => 'edit'))
                    . '\')',
                'class' => 'save',
            ),
            -100
        );

        if ($this->getSplitPayment()->canPay())
            $this->_addButton(
                'payNow',
                array(
                    'label' => Mage::helper('adminhtml')->__('Pay now'),
                    'onclick' =>
                        'run(\'' . $this->getUrl('*/*/payNow', array('_current' => true, 'back' => 'edit')) . '\')',
                    'class' => 'go',
                ),
                -120
            );

        $this->_formScripts[] = "
            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
       		
       		function run(url){
                editForm.submit(url);
            }
        ";
    }

    /**
     * Retrieve SplitPayment model object
     *
     * @return Allopass_Hipay_Model_SplitPayment
     */
    public function getSplitPayment()
    {
        return Mage::registry('split_payment');
    }

}
