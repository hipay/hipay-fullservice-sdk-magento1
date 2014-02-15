<?php
abstract class Allopass_Hipay_Block_Form_Abstract extends Mage_Payment_Block_Form
{

    /**
     * Retrieve payment configuration object
     *
     * @return Allopass_Hipay_Model_Config
     */
    protected function _getConfig()
    {
        return Mage::getSingleton('hipay/config');
    }

    
    public function getCustomerHasAlias()
    {
    	return $this->getCustomer()->getHipayAliasOneclick() != "";
    	 
    }
    
    public function getCustomer()
    {
    	return Mage::getSingleton('customer/session')->getCustomer();
    }
    
    public function ccExpDateIsValid()
    {
    	return $this->helper('hipay')->checkIfCcExpDateIsValid((int)Mage::getSingleton('customer/session')->getCustomerId());
    }
    
    public function oneClickIsAllowed()
    {
    	$checkoutMethod = Mage::getSingleton('checkout/session')->getQuote()->getCheckoutMethod();
    	 
    	if($checkoutMethod == Mage_Checkout_Model_Type_Onepage::METHOD_GUEST || !$this->allowUseOneClick())
    		return false;
    	 
    	return true;
    	 
    }
    
    public function getQuote()
    {
    	return Mage::getSingleton('checkout/session')->getQuote();
    }
    
    
    public function allowUseOneClick()
    {
    	return $this->getMethod()->getConfigData('allow_use_oneclick');
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
