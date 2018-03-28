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

/**
 *
 *
 * @author      HiPay <support.tpp@hipay.com>
 * @copyright   Copyright (c) 2018 - HiPay
 * @license     https://github.com/hipay/hipay-fullservice-sdk-magento1/blob/master/LICENSE.md
 * @link    https://github.com/hipay/hipay-fullservice-sdk-magento1
 */
class Allopass_Hipay_Block_Checkout_Tokenjs extends Mage_Core_Block_Template
{

    /**
     * Check if credentials are enter in configuration
     */
    function hasPublicCredentials()
    {
        return ($this->getConfig()->getApiTokenJSUsername() && $this->getConfig()->getApiTokenJSPublickey())
        || ($this->getConfig()->getApiTokenJSUsernameTest() && $this->getConfig()->getApiTokenJSPublickeyTest());
    }


    /**
     *
     * @return Allopass_Hipay_Model_Config
     */
    protected function getConfig()
    {
        return Mage::getSingleton('hipay/config');
    }

    protected function getHipayMethodsData()
    {
        //Select only method with cancel orders enabled
        $methodsData = array();
        foreach (Mage::helper('hipay')->getHipayMethods() as $code => $model) {
            $methodInstance = Mage::getModel($model);
            $methodsData[$code] = array(
                'is_test_mode' => $methodInstance->getConfigData('is_test_mode'),
            );
        }
        return $methodsData;
    }

    /**
     * Retrieve array of credit card types
     *
     * @return array
     */
    public function getCcTypes()
    {
        $_types = Mage::getConfig()->getNode('global/payment_hipay/cc/types')->asArray();

        uasort($_types, array('Allopass_Hipay_Model_Config', 'compareCcTypes'));

        $types = array();
        foreach ($_types as $data) {
            if (isset($data['code']) && isset($data['name'])) {
                $types[$data['code']] = $data['name'];
            }
        }
        return $types;
    }

    /**
     * Retrive config json
     *
     * @return string
     */
    public function getConfigJson()
    {
        $data = array(
            "api_tokenjs_username" => $this->getConfig()->getApiTokenJSUsername(),
            "api_tokenjs_publickey" => $this->getConfig()->getApiTokenJSPublickey(),
            "api_tokenjs_username_test" => $this->getConfig()->getApiTokenJSUsernameTest(),
            "api_tokenjs_publickey_test" => $this->getConfig()->getApiTokenJSPublickeyTest(),
            "methods" => $this->getHipayMethodsData(),

        );

        return Mage::helper('core')->jsonEncode($data);
    }

}
