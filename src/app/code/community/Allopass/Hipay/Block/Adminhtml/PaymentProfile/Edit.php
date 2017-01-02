<?php
class Allopass_Hipay_Block_Adminhtml_PaymentProfile_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    /**
     * Initialize cms page edit block
     *
     * @return void
     */
    public function __construct()
    {
        $this->_objectId   = 'profile_id';
        $this->_blockGroup = 'hipay';
        $this->_controller = 'adminhtml_paymentProfile';
        $this->_headerText = $this->__('Payment Profile');
        parent::__construct();
        
        $this->_addButton('saveandcontinue', array(
                'label'     => Mage::helper('adminhtml')->__('Save and Continue Edit'),
                'onclick'   => 'saveAndContinueEdit(\''.$this->getUrl('*/*/save', array('_current'=>true,'back'=>'edit')).'\')',
                'class'     => 'save',
        ), -100);
       
        $this->_formScripts[] = "
            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }
}
