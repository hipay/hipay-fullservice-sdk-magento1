<?php
class Allopass_Hipay_CheckoutController extends Mage_Core_Controller_Front_Action
{
	
	
	
	/**
	 * @return Mage_Core_Controller_Front_Action
	 */
	public function preDispatch() {
		parent::preDispatch();
	}

	
	public function pendingAction()
	{
		$session = $this->getOnepage()->getCheckout();
		if (!$session->getLastSuccessQuoteId()) {
			$this->_redirect('checkout/cart');
			return;
		}
		
		$lastQuoteId = $session->getLastQuoteId();
		$lastOrderId = $session->getLastOrderId();
		$lastRecurringProfiles = $session->getLastRecurringProfileIds();
		if (!$lastQuoteId || (!$lastOrderId && empty($lastRecurringProfiles))) {
			$this->_redirect('checkout/cart');
			return;
		}
		
		$session->clear();
		$this->loadLayout();
		$this->_initLayoutMessages('checkout/session');
        $this->renderLayout();
    }
    
    /**
     * Get one page checkout model
     *
     * @return Mage_Checkout_Model_Type_Onepage
     */
    public function getOnepage()
    {
    	return Mage::getSingleton('checkout/type_onepage');
    }
}