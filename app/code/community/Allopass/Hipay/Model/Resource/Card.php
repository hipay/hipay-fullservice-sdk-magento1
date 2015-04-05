<?php
class Allopass_Hipay_Model_Resource_Card extends Mage_Rule_Model_Mysql4_Rule
{
	public function _construct()
	{
		$this->_init('hipay/card','card_id');
	}
	
	
	
	
}