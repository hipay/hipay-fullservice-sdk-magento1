<?php
class Allopass_Hipay_Model_Rule extends Mage_Rule_Model_Rule
{

  /**
     * Init resource model and id field
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('hipay/rule');
        $this->setIdFieldName('rule_id');
    }

    /**
     * Getter for rule conditions collection
     *
     * @return Mage_CatalogRule_Model_Rule_Condition_Combine
     */
    public function getConditionsInstance()
    {
    	return Mage::getModel('hipay/rule_condition_combine')->setPaymentMethodCode($this->_getPaymentMethodCode());
    }
	
	/**
     * Get rule condition product combine model instance
     *
     * @return Mage_SalesRule_Model_Rule_Condition_Product_Combine
     */
    public function getActionsInstance()
    {
        return Mage::getModel('hipay/rule_condition_product_combine');
    }
    
    public function getConditions()
    {
    	parent::getConditions();
    	
    	$this->_conditions->setPaymentMethodCode($this->_getPaymentMethodCode());

		return $this->_conditions;
    }
    
    protected function _getPaymentMethodCode()
    {
    	return str_replace("/", "_", $this->getConfigPath());
    }
	
}
