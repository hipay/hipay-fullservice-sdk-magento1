<?php
class Allopass_Hipay_CardController extends Mage_Core_Controller_Front_Action
{
	
	/**
	 * Retrieve customer session object
	 *
	 * @return Mage_Customer_Model_Session
	 */
	protected function _getSession()
	{
		return Mage::getSingleton('customer/session');
	}

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
		
		$this->loadLayout();
		$this->_initLayoutMessages('customer/session');
		$this->renderLayout();
		
		return $this;
		
		
	}
	
	public function editAction()
	{
		$card = Mage::getModel('hipay/card');
		// Init card object
		if ($id = $this->getRequest()->getParam('card_id')) {
			$card->load($id);
			if ($card->getCustomerId() != Mage::getSingleton('customer/session')->getCustomerId()) {
				$this->_redirect('*/*');
				return;
			}
			else {
				Mage::register('current_card', $card);
			}
		}
	
		$this->loadLayout();
		$this->_initLayoutMessages('customer/session');
		$navigationBlock = $this->getLayout()->getBlock('customer_account_navigation');
		if ($navigationBlock) {
			$navigationBlock->setActive('hipay/card');
		}
		$this->renderLayout();

		return $this;
	
	
	}
	
	public function editPostAction()
	{
	
		 if (!$this->_validateFormKey()) {
            return $this->_redirect('*/*/');
        }
        // Save data
        if ($this->getRequest()->isPost()) {

            $customer = $this->_getSession()->getCustomer();
            $card = Mage::getModel('hipay/card');
            $cardId = $this->getRequest()->getParam('id');
            if ($cardId) {
            	$existsCard = $card->load($cardId);
            	if ($existsCard->getId() && $existsCard->getCustomerId() == $customer->getId()) {
            		$card->setId($existsCard->getId());
            	}
            }
            
            if(!$card->getId())
            {
            	$this->_getSession()->addError("This card no longer exists!");
            	return $this->_redirectError(Mage::getUrl('*/*/'));
            }
           
            try {

                $is_default = $this->getRequest()->getPost('is_default');
            	if($is_default)
            	{
            		$cardsByDefault = Mage::getModel('hipay/card')->getCollection()->addFieldToFilter('customer_id',$customer->getId())
            													->addFieldToFilter('is_default',1);
            		foreach ($cardsByDefault as $c) {
            			$c->setIsDefault(0)->save();
            		}
            	}
            	
            	$card->setName($this->getRequest()->getPost('name'));
            	$card->setIsDefault(empty($is_default) ? 0 : 1);
            	
            	$card->save();
            	$this->_getSession()->addSuccess($this->__('The card has been saved.'));
            	$this->_redirectSuccess(Mage::getUrl('*/*/index', array('_secure'=>true)));
            	
            	return $this;

            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->setCardFormData($this->getRequest()->getPost())
                    ->addException($e, $e->getMessage());
            } catch (Exception $e) {
                $this->_getSession()->setCardFormData($this->getRequest()->getPost())
                    ->addException($e, $this->__('Cannot save card.'));
            }
        }

        return $this->_redirectError(Mage::getUrl('*/*/edit', array('card_id' => $card->getId())));
	
	}
	
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