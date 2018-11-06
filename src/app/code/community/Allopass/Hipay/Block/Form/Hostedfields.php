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
class Allopass_Hipay_Block_Form_Hostedfields extends Allopass_Hipay_Block_Form_Abstract
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('hipay/form/hostedfields.phtml');
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        $fingerprint = $this->getLayout()->createBlock('hipay/checkout_fingerprint', 'hipay.hf.checkout.fingerprint');
        $this->setChild('hipay_fingerprint', $fingerprint);

        $oneclick = $this->getLayout()->createBlock('hipay/checkout_oneclick', 'hipay.hf.checkout.oneclick');
        $this->setChild('hipay_oneclick', $oneclick);

        return $this;
    }

    /**
     * Retrieve availables credit card types
     *
     * @return array
     */
    public function getCcAvailableTypes()
    {
        $types = $this->_getConfig()->getCcTypes();
        if ($method = $this->getMethod()) {
            $availableTypes = $method->getConfigData('cctypes');
            if ($availableTypes) {
                $availableTypes = explode(',', $availableTypes);


                foreach ($types as $code => $name) {
                    if (!in_array($code, $availableTypes)) {
                        unset($types[$code]);
                    }
                }

                $ordered = array();
                foreach ($availableTypes as $key) {
                    if (array_key_exists($key, $types)) {
                        $ordered[$key] = $types[$key];
                        unset($types[$key]);
                    }
                }

                return $ordered;
            }
        }

        return $types;
    }

    /**
     * Retrieve config json
     *
     * @return mixed
     * @throws Mage_Core_Model_Store_Exception
     */
    public function getConfigJson()
    {
        $data = array(
            "api_tokenjs_username" => $this->getConfig()->getApiTokenJSUsername(Mage::app()->getStore()),
            "api_tokenjs_publickey" => $this->getConfig()->getApiTokenJSPublickey(Mage::app()->getStore()),
            "api_tokenjs_username_test" => $this->getConfig()->getApiTokenJSUsernameTest(Mage::app()->getStore()),
            "api_tokenjs_publickey_test" => $this->getConfig()->getApiTokenJSPublickeyTest(Mage::app()->getStore()),
            "testMode" => $this->getMethod()->getConfigData('is_test_mode'),
            "lang" => Mage::app()->getLocale()->getLocaleCode(),
            "isOneClick" => $this->oneClickIsAllowed(),
            "defaultLastname" => $this->getQuote()->getCustomerLastname(),
            "defaultFirstname" => $this->getQuote()->getCustomerFirstname(),
            "style" => array(
                "base" => array(
                    "color" => $this->getMethod()->getConfigData('color'),
                    "fontFamily" => $this->getMethod()->getConfigData('fontFamily'),
                    "fontSize" => $this->getMethod()->getConfigData('fontSize'),
                    "fontWeight" => $this->getMethod()->getConfigData('fontWeight'),
                    "placeholderColor" => $this->getMethod()->getConfigData('placeholderColor'),
                    "caretColor" => $this->getMethod()->getConfigData('caretColor'),
                    "iconColor" => $this->getMethod()->getConfigData('iconColor')
                )
            )
        );

        return Mage::helper('core')->jsonEncode($data);
    }

    /**
     *
     * @return Allopass_Hipay_Model_Config
     */
    protected function getConfig()
    {
        return Mage::getSingleton('hipay/config');
    }
}
