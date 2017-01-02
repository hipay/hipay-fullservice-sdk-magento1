<?php
class Allopass_Hipay_Model_Resource_PaymentProfile extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {
        $this->_init('hipay/paymentProfile', 'profile_id');
    }
}
