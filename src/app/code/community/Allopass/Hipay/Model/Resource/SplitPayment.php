<?php
class Allopass_Hipay_Model_Resource_SplitPayment extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {
        $this->_init('hipay/splitPayment', 'split_payment_id');
    }
}
