<?php


/**
 * Hipay resource collection model
 *
 */
class Allopass_Hipay_Model_Resource_PaymentProfile_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{

    /**
     * Set resource model and determine field mapping
     */
    protected function _construct()
    {
        $this->_init('hipay/paymentProfile');
    }


    public function toOptionArray()
    {
        return $this->_toOptionArray('profile_id');
    }

    /**
     * Add filtering by profile ids
     *
     * @param mixed $profileIds
     * @return Allopass_Hipay_Model_Resource_PaymentProfile_Collection
     */
    public function addIdsToFilter($profileIds)
    {
        $this->addFieldToFilter('main_table.profile_id', $profileIds);
        return $this;
    }

}
