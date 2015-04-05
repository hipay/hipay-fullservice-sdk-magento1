<?php


/**
 * Hipay resource collection model
 *
 */
class Allopass_Hipay_Model_Resource_Card_Collection extends Mage_Rule_Model_Mysql4_Rule_Collection
{ 

    /**
     * Set resource model and determine field mapping
     */
    protected function _construct()
    {
        $this->_init('hipay/card');
    }
    
}
