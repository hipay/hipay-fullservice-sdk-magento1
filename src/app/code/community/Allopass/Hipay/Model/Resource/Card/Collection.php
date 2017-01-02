<?php


/**
 * Hipay resource collection model
 *
 */
class Allopass_Hipay_Model_Resource_Card_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{

    /**
     * Set resource model and determine field mapping
     */
    protected function _construct()
    {
        $this->_init('hipay/card');
    }
}
