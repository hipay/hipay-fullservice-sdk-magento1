<?php
class Allopass_Hipay_Model_Card extends Mage_Core_Model_Abstract
{
	
	const STATUS_DISABLED = 0;
	const STATUS_ENABLED = 1;

  /**
     * Init resource model and id field
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('hipay/card');
        $this->setIdFieldName('card_id');
    }

   
	
}
