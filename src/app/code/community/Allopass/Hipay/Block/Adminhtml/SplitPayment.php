<?php
class Allopass_Hipay_Block_Adminhtml_SplitPayment extends Mage_Adminhtml_Block_Widget_Grid_Container
{
	public function __construct()
	{
		$this->_controller = 'adminhtml_splitPayment';
		$this->_blockGroup = 'hipay';
		$this->_headerText = $this->__('HiPay Split Payments');
	
		parent::__construct();
		
		$this->_removeButton('add');
	}
}