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
class Allopass_Hipay_Block_Adminhtml_Card_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    /**
     * Initialize cms page edit block
     *
     * @return void
     */
    public function __construct()
    {

        $this->_objectId = 'card_id';
        $this->_blockGroup = 'hipay';
        $this->_controller = 'adminhtml_card';
        $this->_headerText = $this->__('Card Hipay');
        parent::__construct();

        $this->removeButton('delete');


        $this->_addButton(
            'saveandcontinue',
            array(
                'label' => Mage::helper('adminhtml')->__('Save and Continue Edit'),
                'onclick' => 'saveAndContinueEdit(\'' . $this->getUrl(
                        '*/*/save',
                        array('_current' => true, 'back' => 'edit')
                    ) . '\')',
                'class' => 'save',
            ),
            -100
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
     * Get URL for back (reset) button
     *
     * @return string
     */
    public function getBackUrl()
    {
        return $this->getUrl('adminhtml/customer/edit', array('id' => $this->getCard()->getCustomerId()));
    }

    /**
     * Retrieve card model object
     *
     * @return Allopass_Hipay_Model_Card
     */
    public function getCard()
    {
        return Mage::registry('current_card');
    }

}
