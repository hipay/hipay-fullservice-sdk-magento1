<?php


class Allopass_Hipay_Adminhtml_HashingController extends Mage_Adminhtml_Controller_Action
{

    /**
     * @var string
     */
    protected $store;

    /**
     * @var string
     */
    protected $website;

    /**
     *  Get hashing configuration from Hipay Back Office and store it in Magento configuration
     *
     */
    public function synchronizeAction()
    {
        $request = Mage::getModel('hipay/api_request');
        $storeId = $this->_getConfigScopeStoreId();
        $scope = $storeId ? 'stores' : "default";
        $session = $this->_getSession();
        try {
            if ($request->existsCredentials($storeId)) {
                Mage::helper('hipay')->synchronizeSecuritySettings($request, $storeId, $scope, $session);
            } else {
                $this->_getSession()->addError($this->__('You must enter credentials to synchronize your configuration.'));
            }
        } catch (Exception $e) {
            $this->_getSession()->addError($this->__('An error has occured : ' . $e->getMessage()));
        }

        $this->setRedirect();
    }


    /**
     *
     *
     * @return int
     */
    protected function _getConfigScopeStoreId()
    {
        $storeId = Mage_Core_Model_App::ADMIN_STORE_ID;
        $this->store = Mage::app()->getRequest()->getParam('store','');
        $this->website = Mage::app()->getRequest()->getParam('website','');

        if ('' !== $this->store) {
            $storeId = Mage::getModel('core/store')->load($this->store)->getId();
        } elseif ('' !== $this->website) {
            $storeId = Mage::getModel('core/website')->load($this->website)->getDefaultStore()->getId();
        }

        return $storeId;
    }

    /**
     *  Redirect after action ( With current store configuration )
     */
    private function setRedirect()
    {
        $this->_redirect('adminhtml/system_config/edit', array('_secure' => true, 'section' => 'hipay',
            'store' => $this->store, 'website' => $this->website ));
    }

    /**
     * @return mixed
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')
            ->isAllowed('system/config');
    }

}
