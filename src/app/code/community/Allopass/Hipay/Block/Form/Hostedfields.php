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

        $splitpayment = $this->getLayout()->createBlock('hipay/checkout_splitpayment', 'hipay.checkout.splitpayment');
        $this->setChild('hipay_splitpayment', $splitpayment);

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
}
