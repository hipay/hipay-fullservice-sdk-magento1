<?php
class Allopass_Hipay_Adminhtml_CardController extends Mage_Adminhtml_Controller_Action
{
    
    /**
     * Init actions
     *
     * @return Allopass_Hipay_Adminhtml_CardController
     */
    protected function _initAction()
    {
        // load layout, set active menu and breadcrumbs
        $this->loadLayout()
        ->_addBreadcrumb(Mage::helper('hipay')->__('Hipay cards'), Mage::helper('hipay')->__('Hipay cards'))
        ;
        return $this;
    }
    
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
    public function preDispatch()
    {
        parent::preDispatch();
    }
    
    protected function _getCustomer()
    {
        return Mage::registry('current_customer');
    }
    
    public function cardsAction()
    {
        $this->_initCustomer();
        $this->loadLayout()
                ->renderLayout();
        
        return $this;
    }
    
    public function editAction()
    {
        $this->_title($this->__('Hipay'))
        ->_title($this->__('Hipay Card'));
    
        // 1. Get ID and create model
        $id = $this->getRequest()->getParam('card_id');
        $model = Mage::getModel('hipay/card');
    
        // 2. Initial checking
        if ($id) {
            $model->load($id);
            if (! $model->getId()) {
                Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('hipay')->__('This card no longer exists.'));
                $this->_redirect('*/*/');
                return;
            }
        }
    
        $this->_title($model->getId() ? $this->__("Card %s", $model->getName()) : $this->__('New card'));
    
        // 3. Set entered data if was error when we do save
        $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
        if (! empty($data)) {
            $model->setData($data);
        }
    
        // 4. Register model to use later in blocks
        Mage::register('current_card', $model);
    
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
            $model = Mage::getModel('hipay/card');
    
            if ($id = $this->getRequest()->getParam('card_id')) {
                $model->load($id);
            }
    
            $model->setData($data);
                
            // try to save it
            try {
                // save the data
                $model->save();
                    
                // display success message
                Mage::getSingleton('adminhtml/session')->addSuccess(
                Mage::helper('hipay')->__('The card has been saved.'));
                // clear previously saved data from session
                Mage::getSingleton('adminhtml/session')->setFormData(false);
                // check if 'Save and Continue'
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('card_id' => $model->getId(), '_current'=>true));
                    return;
                }
                
                // go to grid
                $this->_redirect('adminhtml/customer/edit', array('id'=>$model->getCustomerId()));
                return;
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            } catch (Exception $e) {
                $this->_getSession()->addException($e,
                        Mage::helper('hipay')->__('An error occurred while saving the card.'));
            }
                
            $this->_getSession()->setFormData($data);
            $this->_redirect('*/*/edit', array('card_id' => $this->getRequest()->getParam('card_id')));
            return;
        }
        $this->_redirect('adminhtml/customer/index');
    }
}
