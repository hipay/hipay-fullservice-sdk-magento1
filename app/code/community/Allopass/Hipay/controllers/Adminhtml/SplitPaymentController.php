<?php

class Allopass_Hipay_Adminhtml_SplitPaymentController extends Mage_Adminhtml_Controller_Action
{
	
	/**
	 * Init actions
	 *
	 * @return Allopass_Hipay_Adminhtml_PaymentProfileController
	 */
	protected function _initAction()
	{
		// load layout, set active menu and breadcrumbs
		$this->loadLayout()
		->_setActiveMenu('sale/hipay_payment')
		->_addBreadcrumb(Mage::helper('hipay')->__('Hipay Split payments'), Mage::helper('hipay')->__('Hipay Split payments'))
		;
		return $this;
	}


	public function indexAction()
	{
		$this->_title($this->__('Hipay'))
		->_title($this->__('Hipay Split payments'));
		
		$this->_initAction()
			->renderLayout();
		
		return $this;
	}

}
