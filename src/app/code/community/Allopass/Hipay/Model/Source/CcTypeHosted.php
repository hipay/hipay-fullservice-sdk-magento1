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
 * Allopass Hipay Credit cards types
 *
 * @author      HiPay <support.tpp@hipay.com>
 * @copyright   Copyright (c) 2018 - HiPay
 * @license     https://github.com/hipay/hipay-fullservice-sdk-magento1/blob/master/LICENSE.md
 * @link    https://github.com/hipay/hipay-fullservice-sdk-magento1
 */
class Allopass_Hipay_Model_Source_CcTypeHosted extends Varien_Object
{
    public function toOptionArray()
    {

        $options = array();

        foreach (Mage::getSingleton('hipay/config')->getCcTypesCodeHipay() as $code => $name) {
            $options[] = array(
                'value' => $code,
                'label' => $name
            );
        }

        return $options;
    }

    public function toConfigOption()
    {
        $types = Mage::getSingleton('hipay/config')->getCcTypesCodeHipay();
        if ($this->getPath()) {
            $configData = Mage::getStoreConfig($this->getPath());
            $availableTypes = explode(",", $configData);
            $ordered = array();
            foreach ($availableTypes as $key) {
                if (array_key_exists($key, $types)) {
                    $ordered[$key] = $types[$key];
                    unset($types[$key]);
                }
            }

            return array_merge($ordered, $types);
        }
        
        return $types;
    }
}
