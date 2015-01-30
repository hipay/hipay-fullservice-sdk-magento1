<?php

/**
 *
 * Allopass Hipay Payments Profiles
 *
 */
class Allopass_Hipay_Model_Source_PaymentProfile extends Varien_Object
{
	
	
	protected $_collection = null;
	
	protected function _getCollection()
	{
		if(is_null($this->_collection))
			$this->_collection =  Mage::getModel('hipay/paymentProfile')->getCollection();
		
		return $this->_collection;
	}
	
	public function splitPaymentsToOptionArray()
	{
		/*$options = array();
		foreach ($this->_getCollection()->addFieldToFilter('payment_type','split_payment') as $profile) {
			$options[$profile->getId()] = $profile->getName();
		}
		
		return $options;
		*/
		return $this->_getCollection()->addFieldToFilter('payment_type','split_payment')->toOptionArray();
	}
	
 	/**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
    	
    	return $this->_getCollection()->toOptionArray();
    	
    }
    


}