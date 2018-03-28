<?php

/**
 * HiPay Fullservice SDK Magento 1
 *
 * 2018 HiPay
 *
 * NOTICE OF LICENSE
 *
 * @author    HiPay <support.tpp@hipay.com>
 * @copyright 2018 HiPay
 * @license   https://github.com/hipay/hipay-fullservice-sdk-magento1/blob/master/LICENSE.md
 */
require_once(dirname(__FILE__) . '/../../Helper/Enum/ScopeConfig.php');

/**
 *
 *
 * @author      HiPay <support.tpp@hipay.com>
 * @copyright   Copyright (c) 2018 - HiPay
 * @license     https://github.com/hipay/hipay-fullservice-sdk-magento1/blob/master/LICENSE.md
 * @link    https://github.com/hipay/hipay-fullservice-sdk-magento1
 */
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
     *  Platforms for configuration
     *
     * @var array
     */
    protected $platforms = array(
        ScopeConfig::PRODUCTION,
        ScopeConfig::TEST,
        ScopeConfig::PRODUCTION_MOTO,
        ScopeConfig::TEST_MOTO
    );

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
        $atLeastCredential = false;

        try {
            foreach ($this->platforms as $platform) {
                $request->setEnvironment($platform);
                if ($request->existsCredentials($storeId)) {
                    $atLeastCredential = true;
                    Mage::helper('hipay')->debug("Call Gateway for synchronize Security Settings for {$platform}");
                    Mage::helper('hipay')->synchronizeSecuritySettings($request, $storeId, $scope, $session);
                }
            }

            if (!$atLeastCredential) {
                $this->_getSession()->addError(
                    $this->__('You must enter credentials to synchronize your configuration.')
                );
            }
        } catch (Exception $e) {
            Mage::logException($e);
            $this->_getSession()->addError(
                $this->__(
                    'An error has occured for environment : "' . ScopeConfig::getLabelFromEnvironment(
                        $platform
                    ) . '"" Message :' . $e->getMessage()
                )
            );
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
        $this->store = Mage::app()->getRequest()->getParam('store', '');
        $this->website = Mage::app()->getRequest()->getParam('website', '');

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
        $this->_redirect(
            'adminhtml/system_config/edit',
            array(
                '_secure' => true,
                'section' => 'hipay',
                'store' => $this->store,
                'website' => $this->website
            )
        );
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
