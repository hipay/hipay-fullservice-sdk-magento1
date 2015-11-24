<?php
class Allopass_Hipay_Block_Adminhtml_PaymentProfile extends Mage_Adminhtml_Block_Widget_Grid_Container
{
	public function __construct()
	{
	
		$this->_controller = 'adminhtml_paymentProfile';
		$this->_blockGroup = 'hipay';
		$this->_headerText = $this->__('Hipay Payment Profiles');
		$this->_addButtonLabel = $this->__('Add payment profile');
	
		parent::__construct();
	}
}