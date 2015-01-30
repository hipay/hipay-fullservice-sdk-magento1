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
    
    public function allowSplitPayment()
    {
    	
    	$checkoutMethod = Mage::getSingleton('checkout/session')->getQuote()->getCheckoutMethod();
    	
    	if($checkoutMethod == Mage_Checkout_Model_Type_Onepage::METHOD_GUEST || !$this->getMethod()->getConfigData('allow_split_payment'))
    		return false;
    	
    	return true;
    }
    
    public function getSplitPaymentProfiles()
    {
    	$profileIds = explode(",", $this->getMethod()->getConfigData('split_payment_profile'));
    	$profiles = Mage::getModel('hipay/paymentProfile')->getCollection()->addIdsToFilter($profileIds);
    	return $profiles;
    	
    }
    
    
    public function allowUseOneClick()
    {
    	return $this->getMethod()->getConfigData('allow_use_oneclick');
    }

    public function getIframeConfig()
    {
    	$iframe['iframe_width'] = $this->getMethod()->getConfigData('iframe_width');
    	$iframe['iframe_height'] = $this->getMethod()->getConfigData('iframe_height');
    	$iframe['iframe_style'] = $this->getMethod()->getConfigData('iframe_style');
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
