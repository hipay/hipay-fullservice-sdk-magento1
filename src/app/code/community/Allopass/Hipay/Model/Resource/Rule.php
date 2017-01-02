<?php



/**
 * Hipay Rule resource model
 */
class Allopass_Hipay_Model_Resource_Rule extends Mage_Rule_Model_Mysql4_Rule
{

    /**
     * Initialize main table and table id field
     */
    protected function _construct()
    {
        $this->_init('hipay/rule', 'rule_id');
    }
}
