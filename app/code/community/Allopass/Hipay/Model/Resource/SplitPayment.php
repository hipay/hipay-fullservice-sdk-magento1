<?php
class Allopass_Hipay_Model_Resource_SplitPayment extends Mage_Rule_Model_Mysql4_Rule
{
	public function _construct()
	{
		$this->_init('hipay/splitPayment','split_payment_id');
	}
	
	
	
	
}