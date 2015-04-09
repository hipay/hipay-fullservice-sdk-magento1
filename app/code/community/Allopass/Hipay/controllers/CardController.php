<?php
class Allopass_Hipay_CardController extends Mage_Core_Controller_Front_Action
{

	/**
	 * @return Mage_Core_Controller_Front_Action
	 */
	public function preDispatch() {
		parent::preDispatch();
	
		if (!Mage::getSingleton('customer/session')->authenticate($this)) {
			$this->setFlag('', 'no-dispatch', true);
		}
		
	}
	
	public function indexAction()
	{
		
		$this->loadLayout()
				->renderLayout();
		
		return $this;
		
		
	}
	
	/*public function viewAction()
	{
	
		$this->loadLayout()
		->renderLayout();
	
		return $this;
	
	
	}*/
	
	public function deleteAction()
	{
		
		// check if we know what should be deleted
		if ($id = $this->getRequest()->getParam('card_id')) {
				
			try {
				// init model and delete
				$model = Mage::getModel('hipay/card');
				$model->load($id);
		
				$model->delete();
				// display success message
				Mage::getSingleton('adminhtml/session')->addSuccess(
				Mage::helper('hipay')->__('The card has been deleted.'));
				// go to grid
				$this->_redirect('*/*/');
				return;
		
			} catch (Exception $e) {
				// display error message
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				// go back to edit form
				$this->_redirect('*/*/edit', array('card_id' => $id));
				return;
			}
		}
		// display error message
		Mage::getSingleton('adminhtml/session')->addError(Mage::helper('hipay')->__('Unable to find a card to delete.'));
		// go to grid
		$this->_redirect('*/*/');	
	
	}
	
		
}