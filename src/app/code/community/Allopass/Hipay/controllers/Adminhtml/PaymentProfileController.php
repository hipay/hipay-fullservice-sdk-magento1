<?php

class Allopass_Hipay_Adminhtml_PaymentProfileController extends Mage_Adminhtml_Controller_Action
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
		->_addBreadcrumb(Mage::helper('hipay')->__('Hipay Payment Profiles'), Mage::helper('hipay')->__('Hipay Payment Profiles'))
		;
		return $this;
	}


	public function indexAction()
	{
		$this->_title($this->__('Hipay'))
		->_title($this->__('Hipay Payment Profiles'));
		
		$this->_initAction()
			->renderLayout();
	}
	
	public function editAction()
	{
		$this->_title($this->__('Hipay'))
		->_title($this->__('Hipay Payment Profiles'));
		
		// 1. Get ID and create model
		$id = $this->getRequest()->getParam('profile_id');
		$model = Mage::getModel('hipay/paymentProfile');
		
		// 2. Initial checking
		if ($id) {
			$model->load($id);
			if (! $model->getId()) {
				Mage::getSingleton('adminhtml/session')->addError(
				Mage::helper('hipay')->__('This payment profile no longer exists.'));
				$this->_redirect('*/*/');
				return;
			}
		}
		
		$this->_title($model->getId() ? $model->getName() : $this->__('New payment profile'));
		
		// 3. Set entered data if was error when we do save
		$data = Mage::getSingleton('adminhtml/session')->getFormData(true);
		if (! empty($data)) {
			$model->setData($data);
		}
		
		// 4. Register model to use later in blocks
		Mage::register('payment_profile', $model);
		
		// 5. Build edit form
		
		$this->_initAction()->renderLayout();
	}
	
	public function newAction()
	{
		$this->_redirect('*/*/edit');
	}
	
	public function saveAction()
	{
		// check if data sent
		if ($data = $this->getRequest()->getPost()) {
			
			//init model and set data
			$model = Mage::getModel('hipay/paymentProfile');
		
			if ($id = $this->getRequest()->getParam('profile_id')) {
				$model->load($id);
			}
		
			$model->setData($data);
			
			// try to save it
			try {
				// save the data
				$model->save();
			
				// display success message
				Mage::getSingleton('adminhtml/session')->addSuccess(
				Mage::helper('hipay')->__('The payment profile has been saved.'));
				// clear previously saved data from session
				Mage::getSingleton('adminhtml/session')->setFormData(false);
				// check if 'Save and Continue'
				if ($this->getRequest()->getParam('back')) {
					$this->_redirect('*/*/edit', array('profile_id' => $model->getId(), '_current'=>true));
					return;
				}
				// go to grid
				$this->_redirect('*/*/');
				return;
			
			} catch (Mage_Core_Exception $e) {
				$this->_getSession()->addError($e->getMessage());
			}
			catch (Exception $e) {
				$this->_getSession()->addException($e,
						Mage::helper('hipay')->__('An error occurred while saving the payment profile.'));
			}
			
			$this->_getSession()->setFormData($data);
			$this->_redirect('*/*/edit', array('profile_id' => $this->getRequest()->getParam('profile_id')));
			return;
			
		}
		$this->_redirect('*/*/');
	}
	
	public function deleteAction()
	{
		// check if we know what should be deleted
		if ($id = $this->getRequest()->getParam('profile_id')) {
			
			try {
				// init model and delete
				$model = Mage::getModel('hipay/paymentProfile');
				$model->load($id);

				$model->delete();
				// display success message
				Mage::getSingleton('adminhtml/session')->addSuccess(
				Mage::helper('hipay')->__('The payment profile has been deleted.'));
				// go to grid
				$this->_redirect('*/*/');
				return;
		
			} catch (Exception $e) {
				// display error message
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				// go back to edit form
				$this->_redirect('*/*/edit', array('profile_id' => $id));
				return;
			}
		}
		// display error message
		Mage::getSingleton('adminhtml/session')->addError(Mage::helper('hipay')->__('Unable to find a payment profile to delete.'));
		// go to grid
		$this->_redirect('*/*/');
	}

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')
            ->isAllowed('system/config');
    }

}
