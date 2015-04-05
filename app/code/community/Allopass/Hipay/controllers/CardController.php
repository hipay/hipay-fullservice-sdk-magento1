<?php
class Allopass_Hipay_CardController extends Mage_Core_Controller_Front_Action
{

	/**
	 * @return Mage_Core_Controller_Front_Action
	 */
	public function preDispatch() {
		parent::preDispatch();
	
	}
	
	public function indexAction()
	{
		
		$this->loadLayout()
				->renderLayout();
		
		return $this;
		
		
	}
	
	public function viewAction()
	{
	
		$this->loadLayout()
		->renderLayout();
	
		return $this;
	
	
	}
	
	public function deleteAction()
	{
	
		$this->_redirect("*/*/index");
	
		return $this;
	
	
	}
	
		
}