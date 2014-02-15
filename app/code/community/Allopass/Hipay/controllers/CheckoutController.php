<?php
class Allopass_Hipay_CheckoutController extends Mage_Core_Controller_Front_Action
{
	
	
	
	/**
	 * @return Mage_Core_Controller_Front_Action
	 */
	public function preDispatch() {
		parent::preDispatch();
		//Mage::log($this->getRequest()->getParams(),null,$this->getRequest()->getActionName() . ".log");
	}

	
	public function pendingAction()
	{
		
	 	$lastQuoteId = $this->getOnepage()->getCheckout()->getLastQuoteId();
        $lastOrderId = $this->getOnepage()->getCheckout()->getLastOrderId();
        $this->getOnepage()->getCheckout()->setErrorMessage("");
        if (!$lastQuoteId || !$lastOrderId) {
            $this->_redirect('checkout/cart');
            return;
        }

        $this->loadLayout();
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