<?php
class Allopass_Hipay_Block_Adminhtml_SplitPayment_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    /**
     * Initialize cms page edit block
     *
     * @return void
     */
    public function __construct()
    {
    
        $this->_objectId   = 'split_payment_id';
        $this->_blockGroup = 'hipay';
        $this->_controller = 'adminhtml_splitPayment';
        $this->_headerText = $this->__('Split Payment');
        parent::__construct();
        
        $this->removeButton('delete');
        
        
       $this->_addButton('saveandcontinue', array(
        		'label'     => Mage::helper('adminhtml')->__('Save and Continue Edit'),
        		'onclick'   => 'saveAndContinueEdit(\''.$this->getUrl('*/*/save', array('_current'=>true,'back'=>'edit')).'\')',
        		'class'     => 'save',
        ), -100);
       
       if($this->getSplitPayment()->canPay())
	        $this->_addButton('payNow', array(
	        		'label'     => Mage::helper('adminhtml')->__('Pay now'),
	        		'onclick'   => 'run(\''.$this->getUrl('*/*/payNow', array('_current'=>true,'back'=>'edit')).'\')',
	        		'class'     => 'go',
	        ), -120);
       
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
