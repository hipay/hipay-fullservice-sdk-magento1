<?php
abstract class Allopass_Hipay_Block_Form_Abstract extends Mage_Payment_Block_Form
{
	/**
	 * 
	 * @var Allopass_Hipay_Model_Resource_Card_Collection
	 */
	protected $_cards = null;

    /**
     * Retrieve payment configuration object
     *
     * @return Allopass_Hipay_Model_Config
     */
    protected function _getConfig()
    {
        return Mage::getSingleton('hipay/config');
    }
    
    public function getCards()
    {
    	if(is_null($this->_cards))
    	{
    		$today = new Zend_Date(Mage::app()->getLocale()->storeTimeStamp());
		
			$currentYear = (int)$today->getYear()->toString("YY");
			$currentMonth = (int)$today->getMonth()->toString("MM");
    		
	    	$this->_cards = Mage::getResourceModel('hipay/card_collection')
	    	->addFieldToSelect('*')
	    	->addFieldToFilter('customer_id', $this->getCustomer()->getId())
	    	->addFieldToFilter('cc_status', Allopass_Hipay_Model_Card::STATUS_ENABLED)
	    	->addFieldToFilter('cc_exp_year', array("gteq"=>$currentYear))
	    	->setOrder('card_id', 'desc')
	    	->setOrder('is_default', 'desc');

	    	foreach ($this->_cards as $card)
	    	{
	    		if($card->ccExpYear == $currentYear && $currentMonth < $card->ccExpMonth)
	    			$this->_cards->removeItemByKey($card->getId());
	    	}
    	}
    	
    	return $this->_cards;
    }

    /**
     * @deprecated since v1.0.9
     * @return boolean
     */
    public function getCustomerHasAlias()
    {
    	return $this->getCustomer()->getHipayAliasOneclick() != "";
    	 
    }
    
    public function getCustomerHasCard()
    {
    	return $this->getCards()->count() > 0;
    
    }
    
    public function getCustomer()
    {
    	return Mage::getSingleton('customer/session')->getCustomer();
    }
    
    public function ccExpDateIsValid()
    {
    	return $this->helper('hipay')->checkIfCcExpDateIsValid((int)Mage::getSingleton('customer/session')->getCustomerId());
    }
    
    /**
     * If checkout method is GUEST oneclick is not allowed
     * Or We check method configuration
     * @return boolean
     */
    public function oneClickIsAllowed()
    {
    	$checkoutMethod = Mage::getSingleton('checkout/session')->getQuote()->getCheckoutMethod();
    	 
    	if($checkoutMethod == Mage_Checkout_Model_Type_Onepage::METHOD_GUEST || !$this->allowUseOneClick())
    		return false;
    	 
    	return true;
    	 
    }
    
    /**
     * @return Mage_Sales_Model_Quote 
     *  
     * */
    public function getQuote()
    {
    	return Mage::getSingleton('checkout/session')->getQuote();
    }
    
    public function allowSplitPayment()
    {
    	
    	$checkoutMethod = $this->getQuote()->getCheckoutMethod();
    	$minAmount = $this->getMethod()->getConfigData('min_order_total_split_payment');
    	
    	if($checkoutMethod == Mage_Checkout_Model_Type_Onepage::METHOD_GUEST || 
    			!$this->getMethod()->getConfigData('allow_split_payment') || 
    			($this->getMethod()->getConfigData('allow_split_payment') && !empty($minAmount) && $minAmount >= $this->getQuote()->getBaseGrandTotal() ))
    		return false;
    	
    	return true;
    }
    
    public function getSplitPaymentProfiles()
    {
    	$profileIds = explode(",", $this->getMethod()->getConfigData('split_payment_profile'));
    	$profiles = Mage::getModel('hipay/paymentProfile')->getCollection()->addIdsToFilter($profileIds);
    	return $profiles;
    	
    }
    
    
    protected function allowUseOneClick()
    {
    	switch ((int)$this->getMethod()->getConfigData('allow_use_oneclick')) {
    		case 0:
    			return false;
    			
    		case 1:
    			/* @var $rule Allopass_Hipay_Model_Rule */
    			
    			$rule = Mage::getModel('hipay/rule')->load($this->getMethod()->getConfigData('filter_oneclick'));
    			if($rule->getId())
    			{
    				/*$objToValidate = new Varien_Object();
    				$objToValidate->setQuoteId($this->getQuote()->getId());
    				$objToValidate->setQuote($this->getQuote());
    				$objToValidate->setCreatedAt($this->getQuote()->getCreatedAt());*/
    				return (int)$rule->validate($this->getQuote());
    			}
    			return true;
    			
    	}
    }

    public function getIframeConfig()
    {
    	$iframe = array();
    	$iframe['iframe_width'] = $this->getMethod()->getConfigData('iframe_width');
    	$iframe['iframe_height'] = $this->getMethod()->getConfigData('iframe_height');
    	$iframe['iframe_style'] = $this->getMethod()->getConfigData('iframe_style');
    	$iframe['iframe_wrapper_style'] = $this->getMethod()->getConfigData('iframe_style');
    	return $iframe;
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    protected function _toHtml()
    {
        Mage::dispatchEvent('payment_form_block_to_html_before', array(
            'block'     => $this
        ));
        return parent::_toHtml();
    }
}
