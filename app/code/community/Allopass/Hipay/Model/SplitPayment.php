<?php
class Allopass_Hipay_Model_SplitPayment extends Mage_Core_Model_Abstract
{
	
	const SPLIT_PAYMENT_STATUS_PENDING = 'pending';
	const SPLIT_PAYMENT_STATUS_FAILED = 'failed';
	const SPLIT_PAYMENT_STATUS_COMPLETE = 'complete';
	
	protected function _construct()
	{
		parent::_construct();
		$this->_init('hipay/splitPayment');
		$this->setIdFieldName('split_payment_id');
	}
	
	
	
}