<?php
class Allopass_Hipay_Adminhtml_CardController extends Mage_Adminhtml_Controller_Action
{
	
	protected function _initCustomer($idFieldName = 'id')
	{
		$this->_title($this->__('Customers'))->_title($this->__('Manage Customers'));
	
		$customerId = (int) $this->getRequest()->getParam($idFieldName);
		$customer = Mage::getModel('customer/customer');
	
		if ($customerId) {
			$customer->load($customerId);
		}
	
		Mage::register('current_customer', $customer);
		return $this;
	}

	/**
	 * @return Mage_Core_Controller_Front_Action
	 */
	public function preDispatch() {
		parent::preDispatch();

		
	}
	
	public function cardsAction()
	{
		$this->_initCustomer();
		$this->loadLayout()
				->renderLayout();
		
		return $this;
		
		
	}
	
}