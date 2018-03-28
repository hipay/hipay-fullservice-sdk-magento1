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
class Allopass_Hipay_Block_Adminhtml_PaymentProfile_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    /**
     * Initialize cms page edit block
     * Allopass_Hipay_Block_Adminhtml_PaymentProfile_Edit constructor.
     */
    public function __construct()
    {

        $this->_objectId = 'profile_id';
        $this->_blockGroup = 'hipay';
        $this->_controller = 'adminhtml_paymentProfile';
        $this->_headerText = $this->__('Payment Profile');
        parent::__construct();

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

        $this->_formScripts[] = "
            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

}
